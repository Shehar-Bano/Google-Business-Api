<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'club_id',
        'name',
        'format',
        'tournament_image',
        'start_date',
        'end_date',
        'registration_deadline',
        'entry_fees',
        'prize_pool',
        'allowed_player',
        'registered_players_count',
        'status',
        'rules',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'date',
            'entry_fees' => 'decimal:2',
            'prize_pool' => 'decimal:2',
            'allowed_player' => 'integer',
            'registered_players_count' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(User::class, 'club_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }
}
