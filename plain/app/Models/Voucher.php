<?php

// app/Models/Voucher.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Voucher extends Model {
    protected $fillable = ['code', 'name', 'discount_type', 'value', 'max_discount_idr', 'min_purchase_idr', 'is_birthday', 'starts_at', 'ends_at', 'usage_limit', 'per_user_limit', 'is_active'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
}
