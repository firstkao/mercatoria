<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderStatusService;
use Illuminate\Console\Command;

class ExpireUnclaimedOrders extends Command
{
    protected $signature = 'orders:hangus-expired';

    protected $description = 'Mark orders past their pickup deadline as expired without refund.';

    public function handle(OrderStatusService $statuses): int
    {
        Order::query()
            ->where('status', OrderStatus::MENUNGGU_PELUNASAN->value)
            ->whereNotNull('pickup_deadline_at')
            ->where('pickup_deadline_at', '<=', now())
            ->chunkById(100, function ($orders) use ($statuses): void {
                foreach ($orders as $order) {
                    $statuses->changeStatus(
                        $order,
                        OrderStatus::HANGUS,
                        'Batas pelunasan 7 hari telah terlewati; pesanan hangus tanpa refund.',
                    );
                }
            });

        $this->info('Expired pickup orders processed.');

        return self::SUCCESS;
    }
}
