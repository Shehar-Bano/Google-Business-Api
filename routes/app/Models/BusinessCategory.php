<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessCategory extends Model
{
    use HasFactory;

    protected $table = 'business_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'status',
    ];

    protected static function booted()
    {
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get subcategories for this category.
     */
    public function subcategories()
    {
        return $this->hasMany(BusinessSubcategory::class, 'category_id');
    }
}
