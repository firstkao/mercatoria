<?php

namespace App\Models;

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

    public const TYPE_VARIANT = 'variant';

    public const AVAIL_TERSEDIA = 'tersedia';

    public const AVAIL_KOSONG = 'kosong';

    protected $fillable = [
        'game_id',
        'developer_id',
        'store_id',
        'merch_category_id',
        'parent_id',                // [FIX #2]
        'name',
        'sku',
        'slug',
        'price_rmb',
        'weight_gram',
        'type',
        'image_path',
        'description_image_path',   // [FIX #1]
        'discount_amount',
        'discount_start',
        'discount_end',
        'availability',
        'is_active',
        'sold_count',
        'is_split_box',
    ];

    // [FIX #3] Default values untuk kolom yang sering null
    protected $attributes = [
        'sold_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'price_rmb'             => 'decimal:2',
            'weight_gram'           => 'integer',
            'discount_amount'       => 'decimal:2',
            'discount_start'        => 'date',
            'discount_end'          => 'date',
            'availability'          => 'string',
            'is_active'             => 'boolean',
            'sold_count'            => 'integer',
            'is_split_box'          => 'boolean',
            'parent_id'             => 'integer',       // [FIX #2]
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
        return $this->belongsToMany(Tag::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function splitBox(): HasOne
    {
        return $this->hasOne(SplitBox::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi order yang menyertakan produk ini — via pivot order_items.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items');
    }

    // ── [FIX #2] Split Box / Duplicate relations ──

    /**
     * Produk asli (untuk split box / salinan).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Semua salinan / split box dari produk ini.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // ──────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────

    /**
     * Jumlah order unik yang menyertakan produk ini.
     */
    public function ordersCount(): int
    {
        return OrderItem::where('product_id', $this->id)
            ->distinct()
            ->count('order_id');
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

    /**
     * Berat efektif (gram) — fallback ke default setting.
     */
    public function effectiveWeight(): int       // [FIX #4]
    {
        return $this->weight_gram
            ?? (int) setting('default_weight_gram', 500);
    }

    /**
     * Apakah ada diskon yang sedang aktif.
     */
    public function hasActiveDiscount(): bool
    {
        // [FIX #5] Treat 0 / negative sebagai "tidak ada diskon"
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

    /**
     * Diskon aktif dalam RMB (0 kalau tidak ada diskon aktif).
     */
    public function activeDiscountAmount(): float
    {
        return $this->hasActiveDiscount() ? (float) $this->discount_amount : 0.0;
    }

    // ──────────────────────────────────────
    // SCOPES (bonus — berguna untuk query)
    // ──────────────────────────────────────

    /**
     * Produk aktif (published).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Produk berdasarkan game.
     */
    public function scopeForGame($query, int $gameId)
    {
        return $query->where('game_id', $gameId);
    }

    /**
     * Exclude produk split box (tampilkan produk utuh saja).
     */
    public function scopeExcludeSplitBox($query)
    {
        return $query->where('is_split_box', false);
    }
}
