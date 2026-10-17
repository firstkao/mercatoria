<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        if (static::class === ActivityLog::class) return;

        static::created(fn ($model) => $model->logActivity('created'));
        static::updated(fn ($model) => $model->logActivity('updated'));
        static::deleted(fn ($model) => $model->logActivity('deleted'));
    }

    public function logActivity(string $action): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => class_basename($this) . ' ' . $action,
            'url' => request()?->fullUrl(),
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'metadata' => $this->sanitizeMetadata(),
        ]);
    }

    protected function sanitizeMetadata(): array
    {
        $data = $this->toArray();
        $hidden = ['password', 'remember_token', 'admin_otp', 'admin_otp_expires_at'];
        foreach ($hidden as $field) unset($data[$field]);
        return $data;
    }
}
