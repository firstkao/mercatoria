<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\Admin\AdminNotificationService;
use App\Services\Order\OrderStatusService;
use Illuminate\Console\Command;

class ExpireUnclaimedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:hangus-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ubah order yang lewat timer 7 hari di Menunggu Pelunasan & Pengambilan menjadi Hangus (PRD 17.5)';

    public function handle(OrderStatusService $statuses): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::MenungguPelunasanPengambilan->value)
            ->whereNotNull('pickup_deadline_at')
            ->where('pickup_deadline_at', '<=', now())
            ->get();

        foreach ($orders as $order) {
            // PRD 41.1: bell admin — order nearing expiry (7 hari lewat di Menunggu Pelunasan).
            app(AdminNotificationService::class)->notify(
                'order_nearing_expiry',
                'Order #'.$order->no_order.' lewat batas 7 hari di Menunggu Pelunasan',
                '/office/orders/'.$order->no_order
            );

            $statuses->changeStatus($order, OrderStatus::Hangus, 'system', 'Lewat 7 hari tanpa CO di marketplace');
        }

        $this->info("{$orders->count()} order berubah menjadi Hangus.");

        return self::SUCCESS;
    }
}
