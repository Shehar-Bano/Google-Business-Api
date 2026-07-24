<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenceImage extends Model
{
    use HasFactory;

    protected $table = 'preferences_images';

    protected $fillable = [
        'preference_id',
        'type',
        'label',
        'image',
    ];

    /**
     * Get the preference that owns this image.
     */
    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }
}
