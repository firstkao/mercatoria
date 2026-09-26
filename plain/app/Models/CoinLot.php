<?php

// app/Models/CoinLot.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CoinLot extends Model {
    protected $fillable = ['user_id', 'source', 'order_id', 'birthday_year', 'amount', 'remaining', 'earned_at', 'expires_at'];
    protected $casts = ['earned_at' => 'datetime', 'expires_at' => 'datetime'];
}
