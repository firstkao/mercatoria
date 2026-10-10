<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'split_box_id',
    'character_name',
    'character_image',
    'sort_order',
    'bundle_required',
    'status',
    'locked_by_user_id',
    'locked_at',
    'approved_at',
])]
class SplitBoxSlot extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_LOCKED    = 'locked';
    public const STATUS_APPROVED  = 'approved';

    protected function casts(): array
    {
        return [
            'bundle_required' => 'boolean',
            'sort_order'      => 'integer',
            'locked_at'       => 'datetime',
            'approved_at'     => 'datetime',
        ];
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(SplitBox::class, 'split_box_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
