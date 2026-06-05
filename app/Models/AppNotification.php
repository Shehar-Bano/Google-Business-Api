<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'user_id',
        'title',
        'description',
        'type',
        'data',
        'role_type',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Polymorphic relation
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * User who triggered the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
