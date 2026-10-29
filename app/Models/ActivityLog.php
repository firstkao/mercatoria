<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'url',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat aktivitas user (PRD 39.2/39.5) — dipanggil dari controller.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function record(string $action, ?string $description = null, ?array $metadata = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'url' => substr((string) request()?->fullUrl(), 0, 255),
            'ip_address' => get_real_ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 2000),
            'metadata' => $metadata,
        ]);
    }
}
