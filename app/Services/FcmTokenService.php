<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Support\Facades\DB;

class FcmTokenService
{
    public function saveForUser(User $user, array $data): UserFcmToken
    {
        return DB::transaction(function () use ($user, $data) {
            $token = $data['fcm_token'];
            $tokenHash = hash('sha256', $token);

            $fcmToken = UserFcmToken::query()->firstOrNew([
                'token_hash' => $tokenHash,
            ]);

            $fcmToken->fill([
                'user_id' => $user->id,
                'token' => $token,
                'token_hash' => $tokenHash,
                'device_type' => $data['device_type'] ?? $fcmToken->device_type,
                'device_id' => $data['device_id'] ?? $fcmToken->device_id,
                'last_used_at' => now(),
            ]);

            $fcmToken->save();

            return $fcmToken->refresh();
        });
    }
}
