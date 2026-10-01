<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type',
    'label',
    'account_number',
    'account_name',
    'instructions',
    'qr_image',
    'sort_order',
    'is_active',
])]
class PaymentMethod extends Model
{
    /** Jenis pembayaran yang didukung (sesuai kolom enum `type`). */
    public const TYPES = ['bank', 'qris', 'barcode'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Label manusiawi untuk tiap jenis metode. */
    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'bank' => 'Transfer Bank',
            'qris' => 'QRIS',
            'barcode' => 'Barcode',
            default => 'Lainnya',
        };
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }
}
