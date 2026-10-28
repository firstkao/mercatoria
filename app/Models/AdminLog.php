<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'model',
        'model_id',
        'old_value',
        'new_value',
        'description',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Catat aksi admin (PRD 39.4) — WAJIB dipanggil dari setiap mutasi admin.
     *
     * @param  array<string, mixed>|null  $oldValue
     * @param  array<string, mixed>|null  $newValue
     */
    public static function record(
        User $admin,
        string $action,
        ?string $model = null,
        ?int $modelId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $description = null,
    ): self {
        return static::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Diff sederhana old vs new dari model untuk keperluan audit.
     *
     * @param  array<int, string>  $keys
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public static function diff(Model $model, array $keys, array $newValues): array
    {
        $old = [];
        $new = [];

        foreach ($keys as $key) {
            $before = $model->getOriginal($key);
            $after = $newValues[$key] ?? $model->getAttribute($key);

            if ($before != $after) {
                $old[$key] = $before;
                $new[$key] = $after;
            }
        }

        return [$old, $new];
    }
}
