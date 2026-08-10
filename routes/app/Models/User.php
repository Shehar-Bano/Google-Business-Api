<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    // Statuses used throughout AuthService and auth flow
    public const STATUS_OTP_PENDING = 'otp_pending';

    public const STATUS_PROFILE_INCOMPLETE = 'profile_incomplete';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    // All statuses (used for validation in API)
    public const STATUSES = [
        self::STATUS_OTP_PENDING,
        self::STATUS_PROFILE_INCOMPLETE,
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_REJECTED,
        self::STATUS_SUSPENDED,
    ];

    // Only statuses shown/updatable in admin panel
    public const ADMIN_STATUSES = [
        self::STATUS_SUSPENDED,
    ];

    // Statuses shown in the statistics cards
    public const ADMIN_STATS_STATUSES = [
        self::STATUS_SUSPENDED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_OTP_PENDING => 'OTP Pending',
        self::STATUS_PROFILE_INCOMPLETE => 'Profile Incomplete',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_SUSPENDED => 'Suspended',
    ];

    // Labels for admin panel only
    public const ADMIN_STATUS_LABELS = [
        self::STATUS_SUSPENDED => 'Suspended',
    ];

    public const STATUS_ICONS = [
        self::STATUS_OTP_PENDING => 'mdi-email-outline',
        self::STATUS_PROFILE_INCOMPLETE => 'mdi-account-edit-outline',
        self::STATUS_PENDING => 'mdi-clock-outline',
        self::STATUS_ACTIVE => 'mdi-account-check-outline',
        self::STATUS_REJECTED => 'mdi-account-cancel-outline',
        self::STATUS_SUSPENDED => 'mdi-account-lock-outline',
    ];

    // Icons for admin panel stats
    public const ADMIN_STATUS_ICONS = [
        self::STATUS_SUSPENDED => 'mdi-account-lock-outline',
    ];

    public const ROLE_PLAYER = 'player';

    public const ROLE_CLUB = 'club';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLES = [
        self::ROLE_PLAYER,
        self::ROLE_CLUB,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
    ];

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
        'role',
        'status',
        'email_verified_at',
        'otp_verified',
        'club_name',
        'owner_manager_name',
        'address',
        'city',
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
        'api_access_token',
        'api_refresh_token',
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

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function socialPages(): HasMany
    {
        return $this->hasMany(SocialPage::class);
    }

    public function instagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
