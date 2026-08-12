<?php

namespace App\Services;

use App\Models\InstagramAccount;
use App\Repositories\SocialAccountRepository;
use Illuminate\Database\Eloquent\Collection;

class InstagramService
{
    public function __construct(
        protected SocialAccountRepository $socialAccountRepo,
        protected MetaGraphService $metaGraphService
    ) {}

    /**
     * Connect or update user's Instagram account via Meta Graph API.
     */
    public function connectAccount(int $userId, array $facebookUser, string $provider = 'instagram'): array
    {
        $accountDetails = [
            'provider' => $provider,
            'provider_user_id' => $facebookUser['id'],
            'name' => $facebookUser['name'],
            'email' => $facebookUser['email'] ?? null,
            'profile_picture' => $facebookUser['avatar'] ?? null,
            'access_token' => $facebookUser['token'],
            'refresh_token' => $facebookUser['refreshToken'] ?? null,
            'token_expires_at' => isset($facebookUser['expiresIn']) ? now()->addSeconds($facebookUser['expiresIn']) : null,
        ];

        // 1. Save or update the base social account (social_accounts table)
        $socialAccount = $this->socialAccountRepo->updateOrCreateAccount($userId, $accountDetails);

        // 2. Fetch and sync user pages (social_pages table)
        $pages = $this->metaGraphService->fetchPages($facebookUser['token']);
        if (! empty($pages)) {
            $this->socialAccountRepo->syncPages($socialAccount, $pages);
        }

        $connectedAccounts = [];

        // 3. For each page, detect and connect linked Instagram accounts (instagram_accounts table)
        foreach ($pages as $page) {
            $igAccount = $this->metaGraphService->fetchLinkedInstagramAccount($page['page_id'], $page['page_access_token']);
            if ($igAccount) {
                $this->socialAccountRepo->syncInstagramAccount($socialAccount, $igAccount);
                $connectedAccounts[] = $igAccount;
            }
        }

        // 4. If direct Instagram login was used without Facebook pages, save direct Instagram account
        if (empty($connectedAccounts) && ! empty($facebookUser['id'])) {
            $directIg = [
                'page_id' => 'instagram_'.$facebookUser['id'],
                'instagram_business_id' => (string) $facebookUser['id'],
                'username' => $facebookUser['name'] ?? 'instagram_business',
                'profile_picture' => $facebookUser['avatar'] ?? null,
            ];
            $this->socialAccountRepo->syncInstagramAccount($socialAccount, $directIg);
            $connectedAccounts[] = $directIg;
        }

        return [
            'social_account' => $socialAccount,
            'instagram_accounts' => $connectedAccounts,
        ];
    }

    /**
     * Get all connected Instagram accounts for user.
     */
    public function getAccounts(int $userId): Collection
    {
        return $this->socialAccountRepo->getInstagramAccounts($userId);
    }

    /**
     * Get the first/connected Instagram account for user.
     */
    public function getConnectedAccount(int $userId): ?InstagramAccount
    {
        return $this->socialAccountRepo->getConnectedInstagram($userId);
    }

    /**
     * Disconnect Instagram account link.
     */
    public function disconnect(int $userId): bool
    {
        return $this->socialAccountRepo->disconnectInstagram($userId);
    }

    /**
     * Publish photo post to connected Instagram Business Account.
     */
    public function publishPost(int $userId, string $caption, ?string $imagePath): ?array
    {
        $igAccount = $this->getConnectedAccount($userId);
        if (! $igAccount) {
            return null;
        }

        $page = $this->socialAccountRepo->findPageById($userId, $igAccount->page_id);
        if (! $page) {
            return null;
        }

        // Build public URL for Instagram API
        $imageUrl = $imagePath;
        if ($imagePath && ! filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $clean = ltrim(str_replace('storage/', '', $imagePath), '/');
            $imageUrl = asset('storage/' . $clean);
        }

        return $this->metaGraphService->publishInstagramPhoto(
            $igAccount->instagram_business_id,
            $page->page_access_token,
            $caption,
            $imageUrl
        );
    }
}

