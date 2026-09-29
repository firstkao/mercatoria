<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['coin_lot_id', 'order_id', 'amount', 'status'])]
class CoinSpend extends Model
{
}