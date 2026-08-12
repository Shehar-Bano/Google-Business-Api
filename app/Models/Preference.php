<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use HasFactory;

    protected $table = 'preferences';

    protected $fillable = [
        'business_id',
        'business_tagline',
        'business_description',
        'different_than_competition',
        'why_visit_us',
        'low_standards_of_industry',
        'solutions_for_low_standards',
        'malpractices_in_industry',
        'solutions_for_malpractices',
        'common_mistakes_by_customers',
        'guidelines_to_customer',
        'nearest_landmark',
        'target_gender',
        'target_age_group',
        'region',
        'model_ethnicity',
        'audience',
        'cta',
        'brand_color',
        'stop_creative_auto_approval',
    ];

    protected $casts = [
        'stop_creative_auto_approval' => 'boolean',
    ];

    /**
     * Get the business that owns these preferences.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the images associated with this preference.
     */
    public function images()
    {
        return $this->hasMany(PreferenceImage::class);
    }

    protected static function booted()
    {
        static::saved(function ($preference) {
            if ($preference->business) {
                \App\Services\BusinessScoreCalculator::recalculate($preference->business);
            }
        });
    }
}
