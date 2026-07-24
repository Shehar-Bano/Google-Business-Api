<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopSellingItem extends Model
{
    use HasFactory;

    protected $table = 'top_selling_items';

    protected $fillable = [
        'business_id',
        'item_name',
        'description',
        'price',
        'media',
    ];

    /**
     * Get the business that owns this top selling item.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
