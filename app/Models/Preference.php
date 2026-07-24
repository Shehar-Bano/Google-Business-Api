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
        'interior_photos',
        'team_photos',
        'target_gender',
        'target_age_group',
        'region',
        'model_ethnicity',
        'audience',
        'cta',
        'stop_creative_auto_approval',
    ];

    protected $casts = [
        'interior_photos' => 'array',
        'team_photos' => 'array',
        'stop_creative_auto_approval' => 'boolean',
    ];

    /**
     * Get the business that owns these preferences.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
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
