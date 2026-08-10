<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TermsCondition extends Model
{
    use HasFactory;

    protected $table = 'terms_conditions';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
    ];

    /**
     * Boot function to auto-generate slug on saving if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }
}
