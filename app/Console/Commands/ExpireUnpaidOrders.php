<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\Order\OrderStatusService;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ubah order yang lewat batas upload bukti bayar (1x24 jam) menjadi Pembayaran Gagal (PRD 17.10)';

    public function handle(OrderStatusService $statuses): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::MenungguPembayaran->value)
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', now())
            ->get();

        foreach ($orders as $order) {
            $statuses->changeStatus($order, OrderStatus::PembayaranGagal, 'system', 'Lewat 1x24 jam tanpa bukti bayar valid');
        }

        $this->info("{$orders->count()} order berubah menjadi Pembayaran Gagal.");

        return self::SUCCESS;
    }
}
