<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\CartReminder;
use App\Models\User;
use App\Notifications\CartReminderNotification;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendCartReminders extends Command
{
    protected $signature = 'cart:send-reminder';

    protected $description = 'Kirim pengingat keranjang via Bell + Email info@ (PRD 16.3)';

    public function handle(UserNotificationService $notifications): int
    {
        $userIds = Cart::query()->distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            // Interval 30 menit per user — jangan spam bila run berdekatan.
            $recentlySent = CartReminder::where('user_id', $userId)
                ->where('created_at', '>', now()->subMinutes(30))
                ->exists();
            if ($recentlySent) {
                continue;
            }

            $itemCount = (int) Cart::where('user_id', $userId)->sum('qty');
            $user = User::find($userId);

            if (! $user || $itemCount === 0) {
                continue;
            }

            $notifications->notify($user, 'cart_reminder', 'Item masih di keranjangmu', '/cart');
            Notification::send($user, new CartReminderNotification($itemCount));
            CartReminder::create(['user_id' => $userId]);

            $this->line('Reminder terkirim ke user #'.$userId);
        }

        return self::SUCCESS;
    }
}
