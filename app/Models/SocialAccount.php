<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'name',
        'email',
        'profile_picture',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'connected_at',
        'status',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
    ];

    /**
     * Get the user who owns this social account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated pages.
     */
    public function pages()
    {
        return $this->hasMany(SocialPage::class, 'social_account_id');
    }

    /**
     * Get the associated Instagram accounts.
     */
    public function instagramAccounts()
    {
        return $this->hasMany(InstagramAccount::class, 'social_account_id');
    }
}
