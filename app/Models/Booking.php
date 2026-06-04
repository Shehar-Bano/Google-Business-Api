<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'club_id',
        'court_id',
        'player_id',
        'slot_id',
        'booking_date',
        'start_time',
        'end_time',
        'booking_status',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'court_price',
        'service_fee',
        'total_amount',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'court_price' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(User::class, 'club_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(CourtTimeSlot::class, 'slot_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(BookingReview::class);
    }
}
