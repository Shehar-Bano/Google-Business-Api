<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleScore extends Model
{
    use HasFactory;

    protected $table = 'google_scores';

    protected $fillable = [
        'business_id',
        'name',
        'points',
    ];

    /**
     * Get the business that owns this score.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
