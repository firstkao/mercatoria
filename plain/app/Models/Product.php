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
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const TAGS = [
        'presale' => 'Presale',
        'limited' => 'Limited',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Developer, $this>
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    /**
     * @return BelongsTo<ShippingTier, $this>
     */
    public function shippingTier(): BelongsTo
    {
        return $this->belongsTo(ShippingTier::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function tagLabel(): ?string
    {
        return self::TAGS[$this->tag] ?? null;
    }

    /**
     * Determine whether the strike-through price should be shown right now.
     */
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
}