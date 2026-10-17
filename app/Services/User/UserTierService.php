<?php

namespace App\Services\User;

use App\Enums\TierLevel;
use App\Models\User;

class UserTierService
{
    public function updateTier(User $user): bool
    {
        if (! $user->customer) return false;

        $customer = $user->customer;
        $oldTier = $customer->tier;

        $totalBelanja = $user->orders()
            ->where('status', 'selesai')
            ->sum('total');

        $newTier = TierLevel::fromSpending((float) $totalBelanja)->value;

        $customer->update([
            'tier' => $newTier,
            'total_belanja' => $totalBelanja,
        ]);

        $tierOrder = [
            TierLevel::SILVER->value => 1,
            TierLevel::GOLD->value => 2,
            TierLevel::PLATINUM->value => 3,
        ];

        return ($tierOrder[$newTier] ?? 0) > ($tierOrder[$oldTier] ?? 0);
    }

    public function updateAllTiers(): array
    {
        $result = ['total' => 0, 'upgraded' => 0];

        User::whereHas('customer')
            ->with('customer')
            ->chunk(100, function ($users) use (&$result) {
                foreach ($users as $user) {
                    $result['total']++;
                    if ($this->updateTier($user)) $result['upgraded']++;
                }
            });

        return $result;
    }
}
