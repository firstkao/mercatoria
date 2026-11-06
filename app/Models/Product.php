<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    public const TYPE_SINGLE = 'single';

    public const TYPE_VARIABLE = 'variable';

    public const AVAIL_TERSEDIA = 'tersedia';

    public const AVAIL_KOSONG = 'kosong';

    protected $fillable = [
        'game_id',
        'developer_id',
        'store_id',
        'merch_category_id',
        'parent_id',
        'name',
        'sku',
        'slug',
        'price_rmb',
        'weight_gram',
        'type',
        'image_path',
        'description_image_path',
        'discount_amount',
        'discount_start',
        'discount_end',
        'availability',
        'is_active',
        'sold_count',
        'is_split_box',
    ];

    protected $attributes = [
        'sold_count' => 0,
        'type' => self::TYPE_SINGLE,
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
            'is_split_box' => 'boolean',
            'parent_id' => 'integer',
        ];
    }

    // ──────────────────────────────────────
    // RELATIONS
    // ──────────────────────────────────────

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
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
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id', 'desc');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('position');
    }

    public function splitBox(): HasOne
    {
        return $this->hasOne(SplitBox::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // ──────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }

    public function isSingle(): bool
    {
        return $this->type === self::TYPE_SINGLE;
    }

    public function ordersCount(): int
    {
        return OrderItem::where('product_id', $this->id)
            ->distinct()
            ->count('order_id');
    }

    public function effectiveStore(): ?Store
    {
        if ($this->store_id && $this->store) {
            return $this->store;
        }

        return $this->game?->defaultStore;
    }

    public function effectiveWeight(): int
    {
        return $this->weight_gram
            ?? (int) setting('default_weight_gram', 500);
    }

    public function hasActiveDiscount(): bool
    {
        if (! $this->discount_amount || (float) $this->discount_amount <= 0) {
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

    public function activeDiscountAmount(): float
    {
        return $this->hasActiveDiscount() ? (float) $this->discount_amount : 0.0;
    }

    // ──────────────────────────────────────
    // SCOPES
    // ──────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForGame(Builder $query, int $gameId): Builder
    {
        return $query->where('game_id', $gameId);
    }

    public function scopeExcludeSplitBox(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('is_split_box')->orWhere('is_split_box', false);
        });
    }
}
