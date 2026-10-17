<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;

class SendPickupDeadlineReminders extends Command
{
    protected $signature = 'orders:send-pickup-reminders';

    protected $description = 'Send idempotent H-1 pickup deadline reminders to customers.';

    public function handle(): int
    {
        $from = now()->addHours(23);
        $until = now()->addHours(24);
        Order::query()
            ->where('status', OrderStatus::MENUNGGU_PELUNASAN->value)
            ->whereBetween('pickup_deadline_at', [$from, $until])
            ->with('user')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    $alreadySent = $order->user->notifications()
                        ->where('type', NotificationType::ORDER->value)
                        ->where('meta->order_id', $order->id)
                        ->where('meta->reminder_key', 'pickup_h1')
                        ->exists();
                    if ($alreadySent) {
                        continue;
                    }

                    UserNotificationService::push(
                        $order->user_id,
                        NotificationType::ORDER,
                        'Batas pelunasan besok — H-1',
                        "Selesaikan pelunasan pesanan {$order->order_no} sebelum {$order->pickup_deadline_at?->format('d M Y H:i')} agar pesanan tidak hangus.",
                        route('orders.show', $order),
                        subtype: 'pickup_h1',
                        meta: ['order_id' => $order->id, 'reminder_key' => 'pickup_h1'],
                    );
                }
            });

        $this->info('Pickup deadline reminders processed.');

        return self::SUCCESS;
    }
}
