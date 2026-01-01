<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Invoice;
use App\Models\PackageSubscription;
use Illuminate\Support\Facades\DB;

class BookingFulfillmentService
{
    public function fulfillPaidInvoice(Invoice $invoice, int $actorId): void
    {
        if ($invoice->invoiceable_type !== Booking::class) {
            return;
        }

        DB::transaction(function () use ($invoice, $actorId) {

            /** @var Booking|null $booking */
            $booking = Booking::query()->where('id', $invoice->invoiceable_id)->lockForUpdate()->first();
            if (!$booking) return;

            // إذا الحجز ملغي/مكتمل لا تعمل شيء
            if (in_array($booking->status, ['cancelled','completed'], true)) {
                return;
            }

            // منع التكرار
            $meta = (array)($invoice->meta ?? []);
            if (!empty($meta['fulfilled'])) {
                return;
            }

            // ✅ لو كان pending → confirmed
            if ($booking->status === 'pending') {
                $from = $booking->status;

                $booking->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'updated_by' => $actorId,
                ]);

                BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'from_status' => $from,
                    'to_status' => 'confirmed',
                    'note' => 'Confirmed after invoice payment',
                    'created_by' => $actorId,
                ]);
            }

            // ✅ خصم غسلة من الباقة إذا كانت مستخدمة ولم تُخصم بعد
            $bmeta = (array)($booking->meta ?? []);
            $covers = (bool)($bmeta['package_covers_service'] ?? false);
            $deducted = (bool)($bmeta['package_deducted'] ?? false);

            if ($covers && !$deducted && $booking->package_subscription_id) {
                $sub = PackageSubscription::query()
                    ->where('id', $booking->package_subscription_id)
                    ->where('user_id', $booking->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($sub && (int)$sub->remaining_washes > 0) {
                    $newRemaining = (int)$sub->remaining_washes - 1;

                    $sub->update([
                        'remaining_washes' => $newRemaining,
                        'updated_at' => now(),
                    ]);

                    $bmeta['package_deducted'] = true;
                    $bmeta['package_deducted_at'] = now()->toDateTimeString();

                    $booking->update([
                        'meta' => $bmeta,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            // علّم الفاتورة fulfilled
            $meta['fulfilled'] = true;
            $meta['fulfilled_at'] = now()->toDateTimeString();

            $invoice->update([
                'meta' => $meta,
                'updated_by' => $actorId,
            ]);
        });
    }
}