<?php

namespace App\Console\Commands;

use App\Services\User\UserTierService;
use Illuminate\Console\Command;

class UpdateCustomerTiers extends Command
{
    protected $signature = 'customer:update-tier';

    protected $description = 'Recalculate spending totals and tiers for all customers';

    public function handle(UserTierService $tiers): int
    {
        $result = $tiers->updateAllTiers();

        $this->info("Updated {$result['total']} customers; {$result['upgraded']} tier upgrade(s).");

        return self::SUCCESS;
    }
}
