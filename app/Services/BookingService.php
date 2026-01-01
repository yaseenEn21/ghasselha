<?php 

namespace App\Services;

use DB;

class BookingService
{
    public function autoCancelIfStillPending(int $bookingId): void
    {
        DB::transaction(function () use ($bookingId) {
            $booking = \App\Models\Booking::query()->lockForUpdate()->find($bookingId);
            if (!$booking) return;

            if ($booking->status !== 'pending') return;

            // إلغاء الفواتير غير المدفوعة للحجز
            \App\Models\Invoice::query()
                ->where('invoiceable_type', \App\Models\Booking::class)
                ->where('invoiceable_id', $booking->id)
                ->where('status', 'unpaid')
                ->update([
                    'status' => 'cancelled',
                    'is_locked' => true,
                    'updated_at' => now(),
                ]);

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason_code' => 'payment_timeout',
                'cancel_reason' => 'Auto-cancel (invoice unpaid)',
                'updated_at' => now(),
            ]);
        });
    }
}