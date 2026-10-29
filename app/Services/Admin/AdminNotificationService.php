<?php

namespace App\Services\Admin;

use App\Models\AdminNotification;
use Illuminate\Support\Collection;

class AdminNotificationService
{
    /**
     * Buat notifikasi bell admin (PRD 41.1).
     */
    public function notify(string $type, string $title, ?string $targetUrl = null): AdminNotification
    {
        return AdminNotification::create([
            'type' => $type,
            'title' => $title,
            'target_url' => $targetUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Data topbar bell admin: 10 notifikasi terbaru + jumlah belum dibaca.
     *
     * @return array{notifications: Collection<int, AdminNotification>, unread: int}
     */
    public function topbarData(): array
    {
        return [
            'notifications' => AdminNotification::orderByDesc('id')->limit(10)->get(),
            'unread' => (int) AdminNotification::where('is_read', false)->count(),
        ];
    }
}
