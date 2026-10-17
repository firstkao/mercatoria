<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NotificationType;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'subtype', 'title', 'message',
        'link_url', 'icon', 'meta', 'is_read', 'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function getTypeEnumAttribute(): NotificationType
    { return NotificationType::from($this->type); }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}
