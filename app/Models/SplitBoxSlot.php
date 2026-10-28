<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBoxSlot extends Model
{
    use HasFactory;

    public const ROLE_NORMAL = 'normal';

    public const ROLE_HOT_1 = 'hot_1';

    public const ROLE_HOT_2 = 'hot_2';

    protected $fillable = [
        'split_box_id',
        'variant_id',
        'split_role',
        'qty_per_box',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty_per_box' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function splitBox(): BelongsTo
    {
        return $this->belongsTo(SplitBox::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function batchSlots(): HasMany
    {
        return $this->hasMany(SplitBoxBatchSlot::class, 'slot_id');
    }

    public function isHot(): bool
    {
        return $this->split_role !== self::ROLE_NORMAL;
    }

    /**
     * Kebutuhan pair (jumlah Normal) untuk slot hot ini (PRD 49.2).
     */
    public function pairRequirement(): int
    {
        return match ($this->split_role) {
            self::ROLE_HOT_1 => 1,
            self::ROLE_HOT_2 => 2,
            default => 0,
        };
    }
}
