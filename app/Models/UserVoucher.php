<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVoucher extends Model
{
    protected $fillable = ['user_id', 'voucher_id', 'status', 'used_at', 'order_id'];

    protected $casts = ['used_at' => 'datetime'];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function voucher(): BelongsTo
    { return $this->belongsTo(Voucher::class); }

    public function order(): BelongsTo
    { return $this->belongsTo(Order::class); }

    public function isActive(): bool
    { return $this->status === 'active'; }
}
