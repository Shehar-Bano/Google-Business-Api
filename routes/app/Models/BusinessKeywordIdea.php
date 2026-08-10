<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessKeywordIdea extends Model
{
    use HasFactory;

    protected $table = 'business_keyword_ideas';

    protected $fillable = [
        'business_id',
        'search_query',
        'keyword',
        'avg_monthly_searches',
        'competition',
        'low_range_bid',
        'high_range_bid',
    ];

    /**
     * Get the business that owns these keyword ideas.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
