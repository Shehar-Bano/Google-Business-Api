<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialPage extends Model
{
    use HasFactory;

    protected $table = 'social_pages';

    protected $fillable = [
        'user_id',
        'social_account_id',
        'page_id',
        'page_name',
        'page_access_token',
        'category',
        'connected_at',
    ];

    protected $casts = [
        'page_access_token' => 'encrypted',
        'connected_at' => 'datetime',
    ];

    /**
     * Get the user who owns this page.
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
     * Get the associated Instagram account.
     */
    public function instagramAccount()
    {
        return $this->hasOne(InstagramAccount::class, 'page_id', 'page_id');
    }
}
