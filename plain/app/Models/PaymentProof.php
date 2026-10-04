<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'payment_method_id',
    'payment_stage',
    'amount_idr',
    'proof_path',
    'status',
    'reject_reason',
    'reviewed_at',
    'resubmit_deadline_at',
    // BUG FIX: OrderController mengisi uploaded_at saat membuat bukti, tapi
    // kolom ini tidak masuk fillable → selalu NULL. Akibatnya urutan
    // latestPaymentProof() tidak bisa diandalkan dan halaman detail bukti
    // (admin/payments/show) error saat memanggil ->timezone() pada null.
    'uploaded_at',
])]
class PaymentProof extends Model
{
    protected function casts(): array
    {
        return [
            'amount_idr' => 'integer',
            'reviewed_at' => 'datetime',
            'resubmit_deadline_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}