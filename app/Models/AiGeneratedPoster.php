<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedPoster extends Model
{
    use HasFactory;

    protected $table = 'ai_generated_posters';

    protected $fillable = [
        'user_id',
        'business_id',
        'poster_id',
        'prompt',
        'generated_title',
        'generated_caption',
        'generated_image',
        'status',
        'generation_status',
        'generation_error',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'scheduled_at',
        'published_at',
        'social_post_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Get the user who requested this generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the business profile context.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Get the base poster template used.
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(Poster::class, 'poster_id');
    }

    /**
     * Get the admin user who approved this generated poster.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the generated image URL dynamically.
     */
    public function getGeneratedImageAttribute($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // If the URL is saved as absolute but contains localhost (due to .env APP_URL config),
        // we resolve it dynamically using the current request's asset helper.
        if (str_contains($value, 'localhost')) {
            $pos = strpos($value, 'storage/');
            if ($pos !== false) {
                return asset(substr($value, $pos));
            }
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset($value);
    }

    /**
     * Get social publish records for this generated poster.
     */
    public function socialPublishes()
    {
        return $this->hasMany(PosterSocialPublish::class, 'ai_generated_post_id');
    }

    /**
     * Get the latest social publish log for this poster.
     */
    public function latestSocialPublish()
    {
        return $this->hasOne(PosterSocialPublish::class, 'ai_generated_post_id')->latestOfMany();
    }
}
