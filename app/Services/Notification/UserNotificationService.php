<?php

namespace App\Services\Notification;

use App\Enums\NotificationType;
use App\Models\UserNotification;

class UserNotificationService
{
    public static function push(
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        ?string $linkUrl = null,
        ?string $subtype = null,
        ?string $icon = null,
        array $meta = []
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $userId,
            'type' => $type->value,
            'subtype' => $subtype,
            'title' => $title,
            'message' => $message,
            'link_url' => $linkUrl,
            'icon' => $icon ?? $type->icon(),
            'meta' => $meta,
        ]);
    }

    public static function unreadCount(int $userId): int
    {
        return UserNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public static function markAllRead(int $userId): int
    {
        return UserNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
