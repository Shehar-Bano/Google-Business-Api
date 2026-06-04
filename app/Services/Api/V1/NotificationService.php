<?php

namespace App\Services\Api\V1;

use App\Models\AppNotification;
use App\Models\User;
use App\Support\ApiErrorCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;

class NotificationService
{
    public function forUser(User $user): Collection
    {
        return AppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    public function markAsRead(User $user, string $notificationId): AppNotification
    {
        $notification = AppNotification::query()
            ->whereKey($notificationId)
            ->where('user_id', $user->id)
            ->first();

        if (! $notification) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Notification does not exist.',
                'error_code' => ApiErrorCode::RECORD_NOT_FOUND,
                'errors' => new \stdClass(),
            ], 404));
        }

        $notification->read_at ??= now();
        $notification->save();

        return $notification;
    }

    public function markAllAsRead(User $user): void
    {
        AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
