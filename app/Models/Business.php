<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $table = 'businesses';

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'phone_number',
        'address',
        'brand_logo',
        'rating',
        'reviews',
        'isVerified',
        'category',
        'google_place_id',
        'top_selling_items',
    ];

    protected $hidden = [
        'google_place_id',
    ];

    protected $casts = [
        'top_selling_items' => 'array',
        'isVerified' => 'boolean',
    ];

    /**
     * Get the user who owns this business.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get offerings (products and services) associated with this business.
     */
    public function offerings()
    {
        return $this->belongsToMany(Offering::class, 'business_offerings', 'business_id', 'offering_id')
                    ->withTimestamps();
    }

    /**
     * Get preferences associated with this business.
     */
    public function preferences()
    {
        return $this->hasOne(Preference::class);
    }

    /**
     * Get estimated scores for this business.
     */
    public function estimatedScores()
    {
        return $this->hasMany(BusinessEstimatedScore::class);
    }

    /**
     * Get raw google scores for this business.
     */
    public function googleScores()
    {
        return $this->hasMany(GoogleScore::class);
    }

    protected static function booted()
    {
        static::saved(function ($business) {
            \App\Services\BusinessScoreCalculator::recalculate($business);
        });
    }
}
