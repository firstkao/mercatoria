<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

#[Fillable(['action', 'metadata', 'ip_address', 'user_agent'])]
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an action performed by a shopper during the given request.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function record(User $user, string $action, Request $request, ?array $metadata = null): self
    {
        return $user->activityLogs()->create([
            'action' => $action,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
    
    public const LABELS = [
        'register' => 'Daftar akun',
        'login' => 'Masuk',
        'logout' => 'Keluar',
        'view_product' => 'Lihat produk',
        'reseller_apply' => 'Daftar reseller',
    ];

    public function label(): string
    {
        return self::LABELS[$this->action] ?? $this->action;
    }

    /**
     * Human-readable detail lines shown in the admin activity log.
     *
     * @param  string|null  $currentProductName  Fallback for entries logged before names were stored.
     * @return array<string, string>
     */
    public function details(?string $currentProductName = null): array
    {
        $metadata = $this->metadata ?? [];

        return match ($this->action) {
            'view_product' => array_filter([
                'Produk' => $metadata['name'] ?? $currentProductName ?? '(produk sudah dihapus)',
                'SKU' => $metadata['sku'] ?? null,
            ]),
            default => [],
        };
    }
}