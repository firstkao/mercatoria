<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
}