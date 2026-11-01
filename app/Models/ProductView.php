<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    use HasFactory;

    // View tracking = append-only event log, tidak pernah update.
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'product_id',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────
    // RELATIONS
    // ──────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ──────────────────────────────────────
    // SCOPES
    // ──────────────────────────────────────

    /**
     * View untuk produk tertentu.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * View oleh user tertentu.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * View hari ini.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('viewed_at', today());
    }

    /**
     * View dalam rentang tanggal.
     */
    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('viewed_at', [$from, $to]);
    }

    // ──────────────────────────────────────
    // STATIC HELPERS
    // ──────────────────────────────────────

    /**
     * Hitung view produk — support tanpa user (guest view).
     */
    public static function countFor(int $productId, ?int $userId = null): int
    {
        $q = static::forProduct($productId);

        if ($userId !== null) {
            $q->forUser($userId);
        }

        return $q->count();
    }
}
