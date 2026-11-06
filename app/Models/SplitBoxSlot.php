<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBoxSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'split_box_id',
        'variant_id',
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
}
