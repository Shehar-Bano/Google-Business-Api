<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\IndexNotificationsRequest;
use App\Http\Requests\Api\V1\Notification\MarkAllNotificationsReadRequest;
use App\Http\Requests\Api\V1\Notification\MarkNotificationReadRequest;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Services\Api\V1\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(IndexNotificationsRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'notifications' => NotificationResource::collection(
                $this->notificationService->forUser($request->user(), $request->string('status')->toString() ?: null)
            ),
        ]);
    }

    public function markAsRead(MarkNotificationReadRequest $request, string $notification_id): JsonResponse
    {
        $notification = $this->notificationService->markAsRead($request->user(), $notification_id);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read successfully.',
            'data' => [
                'notification' => NotificationResource::make($notification),
            ],
        ]);
    }

    public function markAllAsRead(MarkAllNotificationsReadRequest $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read successfully.',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }
}
