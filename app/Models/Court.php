<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Court extends Model
{
    protected $fillable = [
        'club_id',
        'name',
        'type',
        'price_per_hour',
        'capacity',
        'status',
        'description',
        'maintenance_note',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'capacity' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(User::class, 'club_id');
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(CourtTimeSlot::class, 'court_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'court_id');
    }
}
