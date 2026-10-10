<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'requester_user_id',
    'price_per_slot_idr',
    'deadline_at',
    'status',
    'fulfilled_at',
])]
class SplitBox extends Model
{
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_LOCKED    = 'locked';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FULFILLED = 'fulfilled';

    protected function casts(): array
    {
        return [
            'price_per_slot_idr' => 'integer',
            'deadline_at'        => 'datetime',
            'fulfilled_at'       => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SplitBoxSlot::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isFull(): bool
    {
        return $this->slots()->where('status', SplitBoxSlot::STATUS_AVAILABLE)->count() === 0;
    }
}
