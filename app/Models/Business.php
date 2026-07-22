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
        'top_selling_items',
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
}
