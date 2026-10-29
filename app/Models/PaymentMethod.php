<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    public const CODE_BCA = 'bca';

    public const CODE_QRIS = 'qris';

    public const CODE_SHOPEEPAY = 'shopeepay';

    protected $fillable = [
        'code',
        'name',
        'type',
        'fee',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
