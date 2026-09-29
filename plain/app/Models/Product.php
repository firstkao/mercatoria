<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'game_id',
    'developer_id',
    'shipping_tier_id',
    'name',
    'slug',
    'sku',
    'description',
    'tag',
    'sale_starts_at',
    'sale_ends_at',
    'is_published',
    'is_featured',
    'featured_order',
    'best_seller_score',
    'best_seller_rank',
    'exclude_best_seller',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const TAGS = [
        'presale' => 'Presale',
        'limited' => 'Limited',
    ];

    protected function casts(): array
    {
        return [
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'featured_order' => 'integer',
            'best_seller_score' => 'integer',
            'best_seller_rank' => 'integer',
            'exclude_best_seller' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ============================================================
    // SCOPES
    // ============================================================

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    #[Scope]
    protected function onSale(Builder $query): Builder
    {
        return $query
            ->whereNotNull('sale_starts_at')
            ->whereNotNull('sale_ends_at')
            ->where('sale_starts_at', '<=', now())
            ->where('sale_ends_at', '>=', now())
            ->whereHas('variants', fn ($q) => $q
                ->whereNotNull('compare_price_yuan')
                ->whereColumn('compare_price_yuan', '>', 'price_yuan'));
    }

    #[Scope]
    protected function featured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where('is_published', true);
    }

    #[Scope]
    protected function bestSellers(Builder $query): Builder
    {
        return $query
            ->whereNotNull('best_seller_rank')
            ->where('best_seller_rank', '>', 0)
            ->where('is_published', true)
            ->orderBy('best_seller_rank');
    }

    // ============================================================
    // RELASI
    // ============================================================

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function shippingTier(): BelongsTo
    {
        return $this->belongsTo(ShippingTier::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    // ============================================================
    // HELPER
    // ============================================================

    public function tagLabel(): ?string
    {
        return self::TAGS[$this->tag] ?? null;
    }

    public function isOnSale(): bool
    {
        return $this->sale_starts_at !== null
            && $this->sale_ends_at !== null
            && now()->between($this->sale_starts_at, $this->sale_ends_at);
    }

    public function isOutOfStock(): bool
    {
        return $this->variants->every(fn (ProductVariant $variant): bool => ! $variant->isAvailable());
    }

    public function isBestSeller(): bool
    {
        return $this->best_seller_rank !== null && $this->best_seller_rank > 0;
    }

    /**
     * Persentase diskon tertinggi dari varian.
     */
    public function maxDiscountPercent(): int
    {
        $max = $this->variants
            ->filter(fn (ProductVariant $v) => $v->compare_price_yuan && (float) $v->compare_price_yuan > (float) $v->price_yuan)
            ->map(fn (ProductVariant $v) => (int) round((1 - (float) $v->price_yuan / (float) $v->compare_price_yuan) * 100))
            ->max();

        return (int) ($max ?? 0);
    }

    /**
     * Produk serupa: prioritas game → developer → produk terbaru.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function relatedProducts(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        $base = static::query()->published()->where('id', '!=', $this->id);

        $byGame = collect();
        if ($this->game_id) {
            $byGame = (clone $base)
                ->where('game_id', $this->game_id)
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take($limit)
                ->get();
        }

        if ($byGame->count() < $limit && $this->developer_id) {
            $exclude = $byGame->pluck('id')->push($this->id)->all();
            $byDev = (clone $base)
                ->whereNotIn('id', $exclude)
                ->where('developer_id', $this->developer_id)
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take($limit - $byGame->count())
                ->get();
            $byGame = $byGame->concat($byDev);
        }

        if ($byGame->count() < $limit) {
            $exclude = $byGame->pluck('id')->push($this->id)->all();
            $fill = (clone $base)
                ->whereNotIn('id', $exclude)
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take($limit - $byGame->count())
                ->get();
            $byGame = $byGame->concat($fill);
        }

        return $byGame;
    }
}