<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id', 'action', 'model', 'model_id',
        'old_value', 'new_value', 'description', 'ip_address',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(
        string $action,
        ?string $model = null,
        ?int $modelId = null,
        array $old = [],
        array $new = [],
        ?string $description = null
    ): self {
        return static::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_value' => $old,
            'new_value' => $new,
            'description' => $description,
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : request()?->ip(),
        ]);
    }
}
