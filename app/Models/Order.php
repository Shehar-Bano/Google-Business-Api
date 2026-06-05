<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    protected $fillable = [
        'order_id',
        'status',
    ];

    public function detail(): HasOne
    {
        return $this->hasOne(OrderDetail::class);
    }
}
