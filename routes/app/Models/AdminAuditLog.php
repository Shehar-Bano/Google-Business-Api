<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $table = 'admin_audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'description',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Get the admin user who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to log an event.
     */
    public static function log(string $action, ?string $targetType = null, ?string $targetId = null, ?string $description = null, ?array $payload = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'payload' => $payload,
            'ip_address' => request()->ip(),
        ]);
    }
}
