<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'max_auto_batches',
        'auto_open_batch',
    ];

    protected function casts(): array
    {
        return [
            'max_auto_batches' => 'integer',
            'auto_open_batch' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SplitBoxSlot::class)->orderBy('sort_order');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SplitBoxBatch::class)->orderBy('batch_no');
    }
}
