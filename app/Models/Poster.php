<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poster extends Model
{
    use HasFactory;

    protected $table = 'posters';

    protected $fillable = [
        'title',
        'image',
        'status',
    ];

    /**
     * Get the AI generated posters for this template.
     */
    public function aiGeneratedPosters(): HasMany
    {
        return $this->hasMany(AiGeneratedPoster::class, 'poster_id');
    }
}
