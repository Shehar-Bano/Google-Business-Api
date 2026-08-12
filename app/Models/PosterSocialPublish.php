<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterSocialPublish extends Model
{
    use HasFactory;

    protected $table = 'poster_social_publishes';

    protected $fillable = [
        'ai_generated_post_id',
        'user_id',
        'google',
        'facebook',
        'instagram',
        'status',
        'failed_reason',
        'facebook_post_id',
        'instagram_post_id',
        'google_post_id',
        'published_at',
    ];

    protected $casts = [
        'google' => 'boolean',
        'facebook' => 'boolean',
        'instagram' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the AI generated poster record.
     */
    public function aiGeneratedPoster(): BelongsTo
    {
        return $this->belongsTo(AiGeneratedPoster::class, 'ai_generated_post_id');
    }

    /**
     * Get the user who owns this publish log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
