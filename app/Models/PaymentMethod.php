<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'account_number', 'account_name',
        'logo_path', 'qr_image_path', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isQr(): bool
    {
        return $this->type === 'qr';
    }
}
