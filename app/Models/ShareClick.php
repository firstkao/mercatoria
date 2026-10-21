<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareClick extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'ip_address', 'country_code', 'reward_given',
    ];

    protected $casts = ['reward_given' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isFromIndonesia(): bool
    {
        return $this->country_code === 'ID';
    }
}
