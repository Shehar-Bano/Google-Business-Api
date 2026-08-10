<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessSubcategory extends Model
{
    use HasFactory;

    protected $table = 'business_subcategories';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'status',
    ];

    protected static function booted()
    {
        static::saving(function ($subcategory) {
            if (empty($subcategory->slug)) {
                $subcategory->slug = Str::slug($subcategory->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    /**
     * Get offerings under this subcategory.
     */
    public function offerings()
    {
        return $this->hasMany(Offering::class, 'subcategory_id');
    }
}
