<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class RecheckUnblockGrace extends Command
{
    protected $signature = 'spammer:recheck-grace';

    protected $description = 'Re-block Customer yang lewat grace period 7 hari unblock tanpa bayar (PRD 7.8)';

    public function handle(): int
    {
        $count = 0;

        Customer::query()
            ->whereNotNull('unblock_grace_until')
            ->where('unblock_grace_until', '<=', now())
            ->where('current_state', '!=', 'MVP')
            ->each(function (Customer $customer) use (&$count): void {
                $customer->update([
                    'current_state' => 'Blocked',
                    'unblock_grace_until' => null,
                    'unblock_count' => $customer->unblock_count + 1,
                    'state_updated_at' => now(),
                ]);
                $count++;
            });

        $this->info("{$count} akun diblokir kembali.");

        return self::SUCCESS;
    }
}
