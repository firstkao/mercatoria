<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'no_hp',
        'current_state',
        'unblock_count',
        'unblock_grace_until',
        'tier',
        'total_belanja',
        'state_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'unblock_count' => 'integer',
            'unblock_grace_until' => 'datetime',
            'total_belanja' => 'decimal:2',
            'state_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
