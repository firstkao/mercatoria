<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    use HasFactory;

    public const ACTOR_USER = 'user';

    public const ACTOR_ADMIN = 'admin';

    public const ACTOR_SYSTEM = 'system';

    protected $fillable = [
        'order_id',
        'status',
        'changed_by',
        'note',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
