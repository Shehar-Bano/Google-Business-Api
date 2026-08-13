<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'title',
        'status',
        'is_popular',
        'price',
        'billing_period',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
    ];

    /**
     * Get the plan features linked to this subscription plan.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'subscription_plan_features', 'subscription_plan_id', 'feature_id')
            ->withTimestamps();
    }
}
