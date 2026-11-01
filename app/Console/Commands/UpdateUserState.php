<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\OrderStatusLog;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateUserState extends Command
{
    protected $signature = 'user:update-state';

    protected $description = 'Transisi state Customer MVP → Ex → ONS → Blocked + notif H-7 (PRD 7.8/7.9)';

    private const KEPUTUS = ['selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen'];

    public function handle(UserNotificationService $notifications): int
    {
        $changed = 0;

        Customer::query()
            ->whereIn('current_state', ['MVP', 'Ex', 'ONS'])
            ->where(fn ($q) => $q->whereNull('unblock_grace_until')->orWhere('unblock_grace_until', '<=', now()))
            ->with('user')
            ->each(function (Customer $customer) use ($notifications, &$changed): void {
                $user = $customer->user;

                if (! $user || ! $user->orders()->exists()) {
                    return;
                }

                if ($user->orders()->whereNotIn('status', self::KEPUTUS)->exists()) {
                    $target = 'MVP';
                    $days = 0;
                } else {
                    $lastKeputus = OrderStatusLog::query()
                        ->whereIn('order_id', $user->orders()->select('id'))
                        ->whereIn('status', self::KEPUTUS)
                        ->max('created_at');

                    if (! $lastKeputus) {
                        return;
                    }

                    $days = (int) Carbon::parse($lastKeputus)->diffInDays(now());
                    $target = $days > 60 ? 'Blocked' : ($days > 30 ? 'ONS' : 'Ex');
                }

                if ($customer->current_state !== $target) {
                    $customer->update(['current_state' => $target, 'state_updated_at' => now()]);
                    $changed++;
                }

                // PRD 7.8: notif H-7 sebelum Blocked (hari ke-53 dari 60).
                if ($target === 'ONS' && $days === 53) {
                    $notifications->notify($user, 'state_warning', 'Akunmu akan diblokir dalam 7 hari. Selesaikan pesanan untuk tetap aktif.', '/dashboard');
                }
            });

        $this->info("{$changed} state user diperbarui.");

        return self::SUCCESS;
    }
}
