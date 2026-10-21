<?php

namespace App\Models;

use App\Services\Order\ShippingCalculatorService;
use App\Services\Product\ProductSearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'sku', 'store_id', 'description',
        'description_image_path', 'main_image_path',
        'type', 'availability', 'weight_gram', 'is_active',
        'price_rmb', 'shipping_cn_rmb', 'shipping_cn_btm_idr', 'shipping_btm_jkt_idr',
        'discount_amount', 'discount_start', 'discount_end', 'sold_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_rmb' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
        'weight_gram' => 'integer',
    ];

    // ─── Relationships ─────────────────────────
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'product_game');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function merchCategories(): BelongsToMany
    {
        return $this->belongsToMany(MerchCategory::class, 'product_merch_category');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderByDesc('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class, 'product_related', 'product_id', 'related_id'
        );
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Helpers: Store & Weight ──────────────
    public function effectiveStore(): ?Store
    {
        if ($this->store_id && $this->store) {
            return $this->store;
        }
        $game = $this->games->first();
        if ($game && $game->default_store_id) {
            return $game->defaultStore;
        }

        return null;
    }

    public function effectiveWeight(): int
    {
        return $this->weight_gram ?? (int) setting('default_weight_gram', 0);
    }

    // ─── Helpers: Harga ───────────────────────
    public function hasActiveDiscount(): bool
    {
        if (! $this->discount_amount || $this->discount_amount <= 0) {
            return false;
        }
        $now = now();
        if ($this->discount_start && $now->lt($this->discount_start)) {
            return false;
        }
        if ($this->discount_end && $now->gt($this->discount_end)) {
            return false;
        }

        return true;
    }

    public function hargaJual(): float
    {
        return app(ShippingCalculatorService::class)->calculateItemShipping($this)['base_price_bersih'];
    }

    public function hargaSetelahDiskon(): float
    {
        $harga = $this->hargaJual();
        if (! $this->hasActiveDiscount()) {
            return $harga;
        }

        return max(0, $harga - (float) $this->discount_amount);
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->hargaSetelahDiskon();
    }

    public function getDisplayPriceAttribute(): string
    {
        return format_rupiah($this->hargaSetelahDiskon());
    }

    public function getIsPurchasableAttribute(): bool
    {
        return $this->is_active && $this->availability === 'tersedia';
    }

    // ─── Scopes ────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithActiveDiscount($query)
    {
        return $query->whereNotNull('discount_amount')
            ->where('discount_amount', '>', 0)
            ->where(function ($q) {
                $q->whereNull('discount_start')->orWhere('discount_start', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('discount_end')->orWhere('discount_end', '>=', now());
            });
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('sold_count');
    }

    public function scopeBestOffer($query, int $limit = 5)
    {
        return $query->withActiveDiscount()
            ->orderByDesc('discount_amount')
            ->limit($limit);
    }

    public function scopeSearch($query, string $q)
    {
        return app(ProductSearchService::class)->search($query, $q);
    }
}
