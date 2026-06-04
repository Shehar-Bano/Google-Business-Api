<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Support\ApiErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (AppNotification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'description' => $notification->description,
                'type' => $notification->type,
                'role_type' => $notification->role_type ?? $request->user()->role,
                'is_read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Notifications fetched successfully.',
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notification_id): JsonResponse
    {
        $notification = AppNotification::query()
            ->whereKey($notification_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification does not exist.',
                'error_code' => ApiErrorCode::RECORD_NOT_FOUND,
            ], 404);
        }

        $notification->read_at ??= now();
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read successfully.',
            'data' => [
                'id' => $notification->id,
                'is_read' => true,
            ],
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read successfully.',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }
}
