<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessEstimatedScore extends Model
{
    use HasFactory;

    protected $table = 'business_estimated_scores';

    protected $fillable = [
        'business_id',
        'name',
        'points',
    ];

    /**
     * Get the business that owns this estimated score.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
