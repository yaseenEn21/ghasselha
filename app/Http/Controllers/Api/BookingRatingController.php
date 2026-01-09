<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingRatingStoreRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingRatingController extends Controller
{
    /**
     * GET /api/v1/bookings/{booking}/rating
     * يفيد الشاشة: هل يمكن التقييم؟ وهل تم التقييم سابقاً؟
     */
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        if ((int) $booking->user_id !== (int) $user->id) {
            return api_error('Not found', 404);
        }

        $canRate = ($booking->status === 'completed') && is_null($booking->rating);

        return api_success([
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'can_rate' => $canRate,
            'rated' => !is_null($booking->rating),
            'rating' => $booking->rating ? (int) $booking->rating : null,
            'comment' => $booking->rating_comment,
            'rated_at' => $booking->rated_at?->toISOString(),
        ], 'Booking rating status');
    }

    /**
     * POST /api/v1/bookings/{booking}/rating
     */
    public function store(BookingRatingStoreRequest $request, Booking $booking)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        // لازم صاحب الحجز
        if ((int) $booking->user_id !== (int) $user->id) {
            return api_error('Not found', 404);
        }

        // لازم completed
        if ($booking->status !== 'completed') {
            return api_error('You can rate only completed bookings', 409);
        }

        $data = $request->validated();

        $updated = DB::transaction(function () use ($booking, $data, $user) {
            // lock لمنع double submit
            $b = Booking::query()
                ->where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            // مرة واحدة فقط
            if (!is_null($b->rating)) {
                return null; // already rated
            }

            $b->update([
                'rating' => (int) $data['rating'],
                'rating_comment' => $data['comment'] ?? null,
                'rated_at' => now(),
                'updated_by' => $user->id,
            ]);

            return $b;
        });

        if (!$updated) {
            return api_error('You already rated this booking', 409);
        }


        $rating = (int) $request->input('rating');

        DB::table('services')
            ->where('id', $booking->service_id)
            ->update([
                'rating_sum' => DB::raw("rating_sum + {$rating}"),
                'rating_count' => DB::raw("rating_count + 1"),
                'rating_avg' => DB::raw("(rating_sum + {$rating}) / (rating_count + 1)"),
                'rating_last_at' => now(),
            ]);

        return api_success([
            'booking_id' => $updated->id,
            'rating' => (int) $updated->rating,
            'comment' => $updated->rating_comment,
            'rated_at' => $updated->rated_at?->toISOString(),
        ], 'Booking rated successfully', 201);
    }
}
