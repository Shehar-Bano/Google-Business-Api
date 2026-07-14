<?php

namespace App\Services;

use App\Repositories\SocialAccountRepository;

class InstagramService
{
    public function __construct(protected SocialAccountRepository $socialAccountRepo) {}

    /**
     * Disconnect Instagram account link.
     */
    public function disconnect(int $userId): bool
    {
        return $this->socialAccountRepo->disconnectInstagram($userId);
    }
}
