<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'bank_name',
        'account_number',
        'account_name',
        'qr_image_path',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_method', 'code');
    }
}
