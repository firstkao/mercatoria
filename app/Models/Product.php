<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const TYPE_SINGLE = 'single';

    public const TYPE_VARIANT = 'variant';

    public const AVAIL_TERSEDIA = 'tersedia';

    public const AVAIL_KOSONG = 'kosong';

    protected $fillable = [
        'game_id',
        'store_id',
        'merch_category_id',
        'name',
        'sku',
        'slug',
        'price_rmb',
        'weight_gram',
        'type',
        'image_path',
        'description_image_path',
        'description',
        'discount_amount',
        'discount_start',
        'discount_end',
        'availability',
        'is_active',
        'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'price_rmb' => 'decimal:2',
            'weight_gram' => 'integer',
            'discount_amount' => 'decimal:2',
            'discount_start' => 'date',
            'discount_end' => 'date',
            'is_active' => 'boolean',
            'sold_count' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function merchCategory(): BelongsTo
    {
        return $this->belongsTo(MerchCategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Store efektif: override produk, fallback default store game.
     */
    public function effectiveStore(): ?Store
    {
        if ($this->store_id && $this->store) {
            return $this->store;
        }

        return $this->game?->defaultStore;
    }

    public function effectiveWeight(): int
    {
        return $this->weight_gram ?? 0;
    }

    public function hasActiveDiscount(): bool
    {
        if ($this->discount_amount === null) {
            return false;
        }

        $now = now();

        if ($this->discount_start && $now->lt($this->discount_start)) {
            return false;
        }

        if ($this->discount_end && $now->gt($this->discount_end->endOfDay())) {
            return false;
        }

        return true;
    }
}
