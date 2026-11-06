<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBoxBatch extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_FULL = 'full';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'split_box_id',
        'batch_no',
        'status',
        'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'batch_no' => 'integer',
            'closes_at' => 'datetime',
        ];
    }

    public function splitBox(): BelongsTo
    {
        return $this->belongsTo(SplitBox::class);
    }

    public function batchSlots(): HasMany
    {
        return $this->hasMany(SplitBoxBatchSlot::class, 'batch_id');
    }
}
