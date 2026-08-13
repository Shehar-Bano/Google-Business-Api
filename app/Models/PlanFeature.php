<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PlanFeature extends Model
{
    use HasFactory;

    protected $table = 'plan_features';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'description',
    ];

    /**
     * Boot the model. Automatically generate unique slug if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            if (empty($feature->slug)) {
                $feature->slug = static::generateUniqueSlug($feature->name);
            }
        });

        static::updating(function ($feature) {
            if ($feature->isDirty('name') && empty($feature->slug)) {
                $feature->slug = static::generateUniqueSlug($feature->name, $feature->id);
            }
        });
    }

    /**
     * Generate unique slug from name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * The subscription plans that include this feature.
     */
    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscription_plan_features', 'feature_id', 'subscription_plan_id')
            ->withTimestamps();
    }
}
