<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'description',
        'price',
        'phone',
        'address',
        'date',
        'time',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'images' => 'array',
            'price' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
