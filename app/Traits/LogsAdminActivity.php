<?php

namespace App\Traits;

use App\Models\AdminLog;

trait LogsAdminActivity
{
    public function logAdminActivity(
        string $action,
        array $old = [],
        array $new = [],
        ?string $description = null
    ): void {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return;
        }

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'model' => class_basename($this),
            'model_id' => $this->id ?? null,
            'old_value' => $old,
            'new_value' => $new,
            'description' => $description ?? class_basename($this).' '.$action,
            'ip_address' => function_exists('get_real_ip') ? get_real_ip() : request()?->ip(),
        ]);
    }
}
