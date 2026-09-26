<?php

// app/Models/CoinSpend.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CoinSpend extends Model {
    protected $fillable = ['coin_lot_id', 'order_id', 'amount', 'status'];
}
