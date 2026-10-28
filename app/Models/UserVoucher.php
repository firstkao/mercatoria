<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVoucher extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_USED_AT_CHECKOUT = 'used_at_checkout';

    public const STATUS_HANGUS = 'hangus';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'order_id',
        'status',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isUsable(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->voucher->is_active
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
