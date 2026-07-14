<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Offering extends Model
{
    use HasFactory;

    protected $table = 'offerings';

    protected $fillable = [
        'subcategory_id',
        'type',
        'name',
        'slug',
        'keywords',
        'status',
    ];

    protected static function booted()
    {
        static::saving(function ($offering) {
            if (empty($offering->slug)) {
                $offering->slug = Str::slug($offering->name);
            }
        });
    }

    /**
     * Get the subcategory this offering belongs to.
     */
    public function subcategory()
    {
        return $this->belongsTo(BusinessSubcategory::class, 'subcategory_id');
    }

    /**
     * Get businesses associated with this offering.
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_offerings', 'offering_id', 'business_id')
                    ->withTimestamps();
    }
}
