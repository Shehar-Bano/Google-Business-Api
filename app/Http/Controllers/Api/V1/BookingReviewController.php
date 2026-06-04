<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use App\Support\ApiErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'error_code' => ApiErrorCode::VALIDATION_ERROR,
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $booking = Booking::query()
            ->with('review')
            ->whereKey((int) $request->route('booking_id'))
            ->where('player_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking does not exist.',
                'error_code' => ApiErrorCode::RECORD_NOT_FOUND,
            ], 404);
        }

        if ($booking->booking_status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed bookings can be reviewed.',
                'error_code' => ApiErrorCode::VALIDATION_ERROR,
            ], 422);
        }

        if ($booking->review) {
            return response()->json([
                'success' => false,
                'message' => 'Review already submitted for this booking.',
                'error_code' => ApiErrorCode::DUPLICATE_RESOURCE,
            ], 422);
        }

        $review = BookingReview::create([
            'booking_id' => $booking->id,
            'player_id' => $request->user()->id,
            'club_id' => $booking->club_id,
            'court_id' => $booking->court_id,
            'rating' => $request->integer('rating'),
            'review' => $request->input('review'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'data' => [
                'id' => $review->id,
                'booking_id' => $review->booking_id,
                'club_id' => $review->club_id,
                'court_id' => $review->court_id,
                'player_id' => $review->player_id,
                'rating' => $review->rating,
                'review' => $review->review,
                'created_at' => $review->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }
}
