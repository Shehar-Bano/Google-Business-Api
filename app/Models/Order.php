<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_IN_REVIEW,
        self::STATUS_ACTIVE,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_IN_REVIEW => 'In Review',
        self::STATUS_ACTIVE    => 'Active',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_COMPLETED => 'Completed',
    ];

    public const STATUS_ICONS = [
        self::STATUS_IN_REVIEW => 'mdi-clock-outline',
        self::STATUS_ACTIVE    => 'mdi-check-circle-outline',
        self::STATUS_CANCELLED => 'mdi-close-circle-outline',
        self::STATUS_COMPLETED => 'mdi-flag-checkered',
    ];

    protected $fillable = [
        'user_id',
        'order_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(OrderDetail::class);
    }
}
