<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRequest extends Model
{
    use HasFactory;

    protected $table = 'review_requests';

    protected $fillable = [
        'business_id',
        'sender_id',
        'sent_to',
        'phone_number',
        'channel',
        'status',
        'redirection_url',
        'whatsapp_message_id',
        'clicked_at',
        'sent_at',
        'reviewed_at',
        'failure_reason',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'sent_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the business that owns this review request.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the target user (if linked).
     */
    public function sentToUser()
    {
        return $this->belongsTo(User::class, 'sent_to');
    }
}
