<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';

    public const SCHEME_FP = 'fp';

    public const SCHEME_DP = 'dp';

    public const PLATFORM_TOCO = 'toco';

    public const PLATFORM_SHOPEE = 'shopee';

    public const DISCOUNT_COIN = 'coin';

    public const DISCOUNT_VOUCHER = 'voucher';

    protected $fillable = [
        'user_id',
        'no_order',
        'status',
        'scheme',
        'platform',
        'payment_method',
        'subtotal',
        'discount_type',
        'discount_amount',
        'coin_spent',
        'user_voucher_id',
        'total',
        'fee_qris',
        'tagihan_web',
        'estimasi_co_marketplace',
        'fee_co_marketplace',
        'customer_note',
        'admin_note',
        'hangus_at',
        'pickup_deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'coin_spent' => 'integer',
            'total' => 'decimal:2',
            'fee_qris' => 'decimal:2',
            'tagihan_web' => 'decimal:2',
            'estimasi_co_marketplace' => 'decimal:2',
            'fee_co_marketplace' => 'decimal:2',
            'hangus_at' => 'datetime',
            'pickup_deadline_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function userVoucher(): BelongsTo
    {
        return $this->belongsTo(UserVoucher::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }
}
