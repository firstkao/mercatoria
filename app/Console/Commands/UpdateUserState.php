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

                // PRD 7.8: notif H-7 — Ex → ONS di hari ke-23, ONS → Blocked di hari ke-53.
                if ($target === 'Ex' && $days === 23) {
                    $notifications->notify($user, 'state_warning', 'Kamu sudah 23 hari tidak order. Yuk order sebelum jadi ONS.', '/dashboard');
                }

                if ($target === 'ONS' && $days === 53) {
                    $notifications->notify($user, 'state_warning', 'Kamu sudah 53 hari tidak order. Yuk order sebelum akun ke-lock!', '/dashboard');
                }
            });

        $this->info("{$changed} state user diperbarui.");

        return self::SUCCESS;
    }
}
