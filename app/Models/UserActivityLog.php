<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'performed_by',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'session_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getFormattedDescriptionAttribute(): string
    {
        $performer = $this->performedBy?->name ?? 'System';
        $target = $this->user?->name ?? 'Unknown User';

        return match ($this->action) {
            'login' => "{$target} logged in",
            'logout' => "{$target} logged out",
            'created' => "{$performer} created user {$target}",
            'updated' => "{$performer} updated user {$target}",
            'deleted' => "{$performer} deleted user {$target}",
            'credentials_generated' => "{$performer} generated credentials for {$target}",
            'password_reset' => "{$target} reset their password",
            'role_changed' => "{$performer} changed {$target}'s role",
            'locked' => "{$performer} locked user {$target}",
            'unlocked' => "{$performer} unlocked user {$target}",
            default => $this->description ?? $this->action,
        };
    }
}
