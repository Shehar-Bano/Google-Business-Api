<?php

namespace App\Repositories;

use App\Models\SocialAccount;
use App\Models\SocialPage;
use App\Models\InstagramAccount;
use Illuminate\Support\Facades\DB;

class SocialAccountRepository
{
    /**
     * Find or update/create a social account link for a user.
     */
    public function updateOrCreateAccount(int $userId, array $details): SocialAccount
    {
        return SocialAccount::updateOrCreate(
            [
                'user_id' => $userId,
                'provider' => $details['provider'],
            ],
            [
                'provider_user_id' => $details['provider_user_id'],
                'name' => $details['name'],
                'email' => $details['email'] ?? null,
                'profile_picture' => $details['profile_picture'] ?? null,
                'access_token' => $details['access_token'],
                'refresh_token' => $details['refresh_token'] ?? null,
                'token_expires_at' => $details['token_expires_at'] ?? null,
                'connected_at' => now(),
                'status' => 'connected',
            ]
        );
    }

    /**
     * Get a social account for a user by provider.
     */
    public function getAccount(int $userId, string $provider): ?SocialAccount
    {
        return SocialAccount::where('user_id', $userId)
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Disconnect/Delete social account link.
     */
    public function disconnectAccount(int $userId, string $provider): bool
    {
        $account = $this->getAccount($userId, $provider);
        if ($account) {
            return $account->delete();
        }
        return false;
    }

    /**
     * Sync/Bulk Save Social Pages for a social account.
     */
    public function syncPages(SocialAccount $account, array $pages): void
    {
        DB::transaction(function () use ($account, $pages) {
            // Delete old pages that are not in the new set
            $newPageIds = array_column($pages, 'page_id');
            SocialPage::where('social_account_id', $account->id)
                ->whereNotIn('page_id', $newPageIds)
                ->delete();

            foreach ($pages as $page) {
                SocialPage::updateOrCreate(
                    [
                        'social_account_id' => $account->id,
                        'page_id' => $page['page_id'],
                    ],
                    [
                        'user_id' => $account->user_id,
                        'page_name' => $page['page_name'],
                        'page_access_token' => $page['page_access_token'],
                        'category' => $page['category'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * Sync/Save Instagram business account link.
     */
    public function syncInstagramAccount(SocialAccount $account, array $igDetails): void
    {
        InstagramAccount::updateOrCreate(
            [
                'social_account_id' => $account->id,
                'instagram_business_id' => $igDetails['instagram_business_id'],
            ],
            [
                'user_id' => $account->user_id,
                'page_id' => $igDetails['page_id'],
                'username' => $igDetails['username'],
                'profile_picture' => $igDetails['profile_picture'] ?? null,
            ]
        );
    }

    /**
     * Delete Instagram account connection.
     */
    public function disconnectInstagram(int $userId): bool
    {
        return InstagramAccount::where('user_id', $userId)->delete() > 0;
    }
}
