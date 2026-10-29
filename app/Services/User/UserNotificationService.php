<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserNotification;

class UserNotificationService
{
    /**
     * Buat notifikasi bell user (PRD 43).
     */
    public function notify(User $user, string $type, string $title, ?string $targetUrl = null, ?string $message = null): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'target_url' => $targetUrl,
            'is_read' => false,
        ]);
    }
}
