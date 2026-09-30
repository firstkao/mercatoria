<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'label',
    'account_number',
    'account_name',
    'instructions',
    'qr_image', // <-- TAMBAHKAN BARIS INI
    'sort_order',
    'is_active',
])]
class PaymentMethod extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }
}
