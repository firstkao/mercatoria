<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\User\UserNotificationService;
use Illuminate\Console\Command;

class PurgeCrushAccounts extends Command
{
    protected $signature = 'user:purge-crush';

    protected $description = 'Hard delete akun Crush > 30 hari sejak verifikasi email tanpa order (PRD 7.10)';

    public function handle(UserNotificationService $notifications): int
    {
        // PRD 7.8: notif H-7 di hari ke-23 sejak verifikasi email.
        User::query()
            ->where('role', User::ROLE_CRUSH)
            ->whereDate('email_verified_at', now()->subDays(23)->toDateString())
            ->whereDoesntHave('orders')
            ->each(fn (User $user) => $notifications->notify($user, 'state_warning', 'Akun kamu akan dihapus 7 hari lagi. Yuk order sebelum hilang!', '/dashboard'));

        // Akun dengan order apa pun (termasuk Menunggu Pembayaran / Ditahan) tidak pernah dihapus.
        $count = 0;

        User::query()
            ->where('role', User::ROLE_CRUSH)
            ->whereNotNull('email_verified_at')
            ->where('email_verified_at', '<=', now()->subDays(30))
            ->whereDoesntHave('orders')
            ->each(function (User $user) use (&$count): void {
                $user->delete();
                $count++;
            });

        $this->info("{$count} akun Crush dihapus.");

        return self::SUCCESS;
    }
}
