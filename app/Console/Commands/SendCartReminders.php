<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\CartReminder;
use App\Models\User;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendCartReminders extends Command
{
    protected $signature = 'cart:send-reminder';

    protected $description = 'Notify and email customers whose cart still contains items';

    public function handle(ScheduledEmailService $emails): int
    {
        $sent = 0;
        $skipped = 0;

        User::query()->whereHas('carts')->orderBy('id')->chunkById(100, function ($users) use (
            $emails,
            &$sent,
            &$skipped,
        ): void {
            foreach ($users as $user) {
                $reminder = DB::transaction(function () use ($user): ?CartReminder {
                    $lockedUser = User::query()->lockForUpdate()->find($user->id);
                    if (! $lockedUser || ! $lockedUser->carts()->exists()) {
                        return null;
                    }

                    $reminder = CartReminder::query()->where('user_id', $user->id)->lockForUpdate()->first();
                    if ($reminder?->last_sent_at?->gt(now()->subMinutes(30))) {
                        return null;
                    }

                    return $reminder ?? CartReminder::create(['user_id' => $user->id]);
                });

                if (! $reminder) {
                    $skipped++;

                    continue;
                }

                $link = route('cart.index');
                UserNotificationService::push(
                    $user->id,
                    NotificationType::CART,
                    'Ada item yang menunggu di keranjang',
                    'Keranjangmu masih menyimpan item. Kunjungi kembali untuk melanjutkan belanja.',
                    $link,
                    subtype: 'cart_reminder',
                );
                $emails->send($user, 'cart_reminder', 'Item di keranjangmu menunggu', [
                    'nama_user' => $user->name,
                    'email' => $user->email,
                    'link' => $link,
                    'tanggal' => now()->format('d-m-Y'),
                ]);
                $reminder->update([
                    'last_sent_at' => now(),
                    'total_sent' => $reminder->total_sent + 1,
                ]);
                $sent++;
            }
        });

        $this->info("Cart reminders sent={$sent}, skipped within interval={$skipped}.");

        return self::SUCCESS;
    }
}
