<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportOption extends Model
{
    protected $fillable = [
        'title',
        'type',
        'value',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
