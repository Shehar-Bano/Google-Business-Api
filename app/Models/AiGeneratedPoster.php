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
    ];

    protected $casts = [
        'approved_at' => 'datetime',
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
}
