<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Tipe pembayaran (PRD 28: dp / full / settlement / refund; PRD 51: penalty).
     */
    public const TYPE_FULL = 'full';

    public const TYPE_DP = 'dp';

    public const TYPE_SETTLEMENT = 'settlement';

    public const TYPE_REFUND = 'refund';

    public const TYPE_PENALTY = 'penalty';

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'order_id',
        'type',
        'method',
        'amount',
        'file_path',
        'status',
        'verified_by',
        'verified_at',
        'reject_reason',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
