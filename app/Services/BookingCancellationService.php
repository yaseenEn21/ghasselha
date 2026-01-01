<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Invoice;
use App\Models\PackageSubscription;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BookingCancellationService
{
    public function cancel(Booking $booking, string $reason, ?string $note, int $actorId, WalletService $walletService, InvoiceService $invoiceService): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $note, $actorId, $walletService, $invoiceService) {

            $booking = Booking::query()->where('id', $booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status === 'cancelled') {
                return $booking;
            }

            $from = $booking->status;

            // 1) إلغاء أي فواتير unpaid
            Invoice::query()
                ->where('invoiceable_type', Booking::class)
                ->where('invoiceable_id', $booking->id)
                ->where('status', 'unpaid')
                ->update([
                    'status' => 'cancelled',
                    'is_locked' => true,
                    'updated_at' => now(),
                    'updated_by' => $actorId,
                ]);

            // 2) لو فيه مدفوعات مدفوعة → استرجاع للمحفظة افتراضيًا
            $paidAmount = (float) Payment::query()
                ->where('payable_type', Booking::class)
                ->where('payable_id', $booking->id)
                ->where('status', 'paid')
                ->sum('amount');

            if ($paidAmount > 0) {
                // credit wallet
                $walletService->credit(
                    $booking->user,
                    $paidAmount,
                    'refund',
                    ['ar' => 'استرجاع مبلغ الحجز (إلغاء)', 'en' => 'Booking refund (cancel)'],
                    $booking,
                    null,
                    $actorId,
                    ['booking_id' => $booking->id]
                );

                // mark payments refunded
                Payment::query()
                    ->where('payable_type', Booking::class)
                    ->where('payable_id', $booking->id)
                    ->where('status', 'paid')
                    ->update([
                        'status' => 'refunded',
                        'updated_at' => now(),
                    ]);

                // mark invoices refunded
                Invoice::query()
                    ->where('invoiceable_type', Booking::class)
                    ->where('invoiceable_id', $booking->id)
                    ->where('status', 'paid')
                    ->update([
                        'status' => 'refunded',
                        'is_locked' => true,
                        'updated_at' => now(),
                        'updated_by' => $actorId,
                    ]);

                // (اختياري) سجل credit_note
                $invoiceService->createBookingCreditNoteToWallet($booking, $paidAmount, $actorId, [
                    'reason' => 'booking_cancel_refund',
                ]);
            }

            // 3) رجّع غسلة للباقة إذا كانت انخصمت
            $bmeta = (array)($booking->meta ?? []);
            if (!empty($bmeta['package_covers_service']) && !empty($bmeta['package_deducted']) && $booking->package_subscription_id) {
                $sub = PackageSubscription::query()
                    ->where('id', $booking->package_subscription_id)
                    ->where('user_id', $booking->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($sub) {
                    $sub->update([
                        'remaining_washes' => (int)$sub->remaining_washes + 1,
                        'updated_at' => now(),
                    ]);

                    $bmeta['package_deducted'] = false;
                    $booking->update(['meta' => $bmeta, 'updated_by' => $actorId]);
                }
            }

            // 4) حدّث الحجز
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancel_note' => $note,
                'updated_by' => $actorId,
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => $from,
                'to_status' => 'cancelled',
                'note' => $reason . ($note ? ' - '.$note : ''),
                'created_by' => $actorId,
            ]);

            return $booking;
        });
    }
}