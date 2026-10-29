<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\User\UserTierService;
use Illuminate\Console\Command;

class UpdateCustomerTier extends Command
{
    protected $signature = 'customer:update-tier';

    protected $description = 'Evaluasi tier Silver/Gold/Platinum dari total belanja (PRD 37.3)';

    public function handle(UserTierService $tiers): int
    {
        $count = 0;

        foreach (Customer::query()->cursor() as $customer) {
            $user = $customer->user;

            if (! $user) {
                continue;
            }

            $tiers->applyTier($user, (float) $customer->total_belanja);
            $count++;
        }

        $this->info($count.' customer dievaluasi tier-nya.');

        return self::SUCCESS;
    }
}
