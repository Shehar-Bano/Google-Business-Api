<?php

namespace App\Services;

use App\Repositories\SocialAccountRepository;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    public function __construct(
        protected SocialAccountRepository $socialAccountRepo,
        protected MetaGraphService $metaGraphService
    ) {}

    /**
     * Connect or update user Facebook account, fetch pages, and auto-connect linked Instagram profiles.
     */
    public function connectAccount(int $userId, array $facebookUser): SocialAccount
    {
        $accountDetails = [
            'provider' => 'facebook',
            'provider_user_id' => $facebookUser['id'],
            'name' => $facebookUser['name'],
            'email' => $facebookUser['email'] ?? null,
            'profile_picture' => $facebookUser['avatar'] ?? null,
            'access_token' => $facebookUser['token'],
            'refresh_token' => $facebookUser['refreshToken'] ?? null,
            'token_expires_at' => isset($facebookUser['expiresIn']) ? now()->addSeconds($facebookUser['expiresIn']) : null,
        ];

        // 1. Save or update the social account link
        $socialAccount = $this->socialAccountRepo->updateOrCreateAccount($userId, $accountDetails);

        // 2. Fetch and Sync Facebook Pages
        $pages = $this->metaGraphService->fetchPages($facebookUser['token']);
        $this->socialAccountRepo->syncPages($socialAccount, $pages);

        // 3. For every page, detect and auto-connect linked Instagram accounts
        foreach ($pages as $page) {
            $igAccount = $this->metaGraphService->fetchLinkedInstagramAccount($page['page_id'], $page['page_access_token']);
            if ($igAccount) {
                $this->socialAccountRepo->syncInstagramAccount($socialAccount, $igAccount);
            }
        }

        return $socialAccount;
    }

    /**
     * Disconnect Facebook and clean pages/Instagram links.
     */
    public function disconnect(int $userId): bool
    {
        // Spatie cascade rules or SocialAccount model delete cascades pages and instagram_accounts
        return $this->socialAccountRepo->disconnectAccount($userId, 'facebook');
    }
}
