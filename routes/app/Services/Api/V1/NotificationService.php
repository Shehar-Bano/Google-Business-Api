<?php

namespace App\Services\Api\V1;

use App\Models\AppNotification;
use App\Models\User;
use App\Support\ApiErrorCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;

class NotificationService
{
    public function forUser(User $user, ?string $status = null): Collection
    {
        return $user->appNotifications()
            ->when($status === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->when($status === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
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
