<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitBoxBatchSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'slot_id',
        'qty_total',
        'qty_taken',
    ];

    protected function casts(): array
    {
        return [
            'qty_total' => 'integer',
            'qty_taken' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SplitBoxBatch::class, 'batch_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(SplitBoxSlot::class, 'slot_id');
    }

    public function availableQty(): int
    {
        return max(0, $this->qty_total - $this->qty_taken);
    }
}
