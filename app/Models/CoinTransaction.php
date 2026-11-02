<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    use HasFactory;

    /**
     * Tipe transaksi koin.
     * `expired_at` diisi saat earned (PRD 9.3: expired 1 tahun sejak didapat).
     */
    public const TYPE_USED_AT_CHECKOUT = 'used_at_checkout';

    public const TYPE_FORFEIT_AT_CHECKOUT = 'forfeit_at_checkout';

    public const TYPE_EARNED_REFERRAL_REFERRER = 'earned_referral_referrer';

    public const TYPE_EARNED_REFERRAL_REFEREE = 'earned_referral_referee';

    public const TYPE_EARNED_ORDER = 'earned_order';

    public const TYPE_EARNED_SHARE = 'earned_share';

    public const TYPE_EARNED_BIRTHDAY = 'earned_birthday';

    public const TYPE_HANGUS_PAYMENT_FAILED = 'hangus_payment_failed';

    public const TYPE_REFUND_RETURNED = 'refund_returned';

    public const TYPE_EXPIRED = 'expired';

    public const TYPE_ADMIN_GIFT = 'admin_gift';

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
        'expired_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'expired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
