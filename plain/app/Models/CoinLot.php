<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'source', 'order_id', 'birthday_year', 'amount', 'remaining', 'earned_at', 'expires_at'])]
class CoinLot extends Model
{
    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // BUG FIX: model ini tadinya tidak punya relasi sama sekali, padahal
    // ProcessCoinLifecycle memanggil CoinLot::with('user') lalu mengakses
    // $lot->user → BadMethodCallException setiap command coins:lifecycle jalan
    // (scheduler harian gagal total).
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}