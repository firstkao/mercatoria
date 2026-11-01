<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * PRD 23: nomor invoice INV-MER-{4 digit} — sinkron sequence akhir no_order.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (! $order->invoice_number && $order->no_order) {
                $order->invoice_number = setting('invoice_prefix', 'INV-MER').'-'.substr($order->no_order, -4);
            }
        });
    }

    public const SCHEME_FP = 'fp';

    public const SCHEME_DP = 'dp';

    public const PLATFORM_TOCO = 'toco';

    public const PLATFORM_SHOPEE = 'shopee';

    public const DISCOUNT_COIN = 'coin';

    public const DISCOUNT_VOUCHER = 'voucher';

    public const CANCEL_SLOT_SOLD = 'slot_sold';

    protected $fillable = [
        'user_id',
        'no_order',
        'invoice_number',
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
        'note_to_customer',
        'note_to_customer_updated_at',
        'admin_note',
        'cancel_reason',
        'tier_counted_at',
        'tier_counted_amount',
        'penalty_amount',
        'penalty_deadline_at',
        'penalty_paid_at',
        'penalty_unlocked_at',
        'hangus_at',
        'hangus_permanent_at',
        'pickup_deadline_at',
        'payment_deadline_at',
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
            'hangus_permanent_at' => 'datetime',
            'note_to_customer_updated_at' => 'datetime',
            'pickup_deadline_at' => 'datetime',
            'payment_deadline_at' => 'datetime',
            'tier_counted_at' => 'datetime',
            'tier_counted_amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'penalty_deadline_at' => 'datetime',
            'penalty_paid_at' => 'datetime',
            'penalty_unlocked_at' => 'datetime',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at')->orderBy('id');
    }
}
