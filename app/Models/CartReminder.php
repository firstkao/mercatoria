<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartReminder extends Model
{
    protected $fillable = ['user_id', 'last_sent_at', 'total_sent'];

    protected $casts = ['last_sent_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
