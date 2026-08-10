<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramAccount extends Model
{
    use HasFactory;

    protected $table = 'instagram_accounts';

    protected $fillable = [
        'user_id',
        'social_account_id',
        'page_id',
        'instagram_business_id',
        'username',
        'profile_picture',
    ];

    /**
     * Get the user who owns this account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the social account.
     */
    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }

    /**
     * Get the associated social page.
     */
    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'page_id', 'page_id');
    }
}
