<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'otp_verified',
        'club_name',
        'owner_manager_name',
        'address',
        'city',
        'city_id',
        'number_of_courts',
        'working_hours',
        'club_logo',
        'facilities',
        'profile_image',
        'dob',
        'gender',
        'playing_level',
        'primary_hand',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_verified' => 'boolean',
            'facilities' => 'array',
            'dob' => 'date',
        ];
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class, 'club_id');
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'club_id');
    }

    public function bookingsAsClub(): HasMany
    {
        return $this->hasMany(Booking::class, 'club_id');
    }

    public function bookingsAsPlayer(): HasMany
    {
        return $this->hasMany(Booking::class, 'player_id');
    }

    public function tournamentRegistrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class, 'player_id');
    }

    public function bookingReviewsAsPlayer(): HasMany
    {
        return $this->hasMany(BookingReview::class, 'player_id');
    }

    public function bookingReviewsAsClub(): HasMany
    {
        return $this->hasMany(BookingReview::class, 'club_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
