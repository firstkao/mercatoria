<?php

namespace App\Services\User;

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\UserVoucher;

class DiscountForfeitureService
{
    /**
     * SATU PINTU hangus permanen koin & voucher checkout (PRD 9.3 / 17.10 / 49.7).
     *
     * Dipanggil dari OrderStatusService saat order masuk status pembayaran_gagal
     * atau hangus — sehingga semua jalur (timeout 24 jam, ditolak tanpa upload
     * ulang, manual admin, slot_sold) otomatis tercakup.
     *
     * TANPA UNDO: saldo koin tidak dikembalikan, voucher menjadi HANGUS.
     * Idempoten — aman dipanggil berulang untuk order yang sama.
     */
    public function forfeitUnpaidCheckoutDiscounts(Order $order): void
    {
        if ($order->coin_spent > 0) {
            $this->forfeitCoins($order);
        }

        if ($order->user_voucher_id) {
            $this->forfeitVoucher($order);
        }

        if ($order->coin_spent > 0 || $order->user_voucher_id) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => OrderStatusLog::ACTOR_SYSTEM,
                'note' => 'Koin/voucher hangus permanen (PRD 9.3 — tanpa undo)',
            ]);
        }
    }

    private function forfeitCoins(Order $order): void
    {
        $alreadyForfeited = CoinTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', CoinTransaction::TYPE_FORFEIT_AT_CHECKOUT)
            ->exists();

        if ($alreadyForfeited) {
            return;
        }

        $coin = Coin::where('user_id', $order->user_id)->lockForUpdate()->first();

        // amount 0 = saldo TIDAK dikembalikan; baris ini murni audit hangus.
        CoinTransaction::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => CoinTransaction::TYPE_FORFEIT_AT_CHECKOUT,
            'amount' => 0,
            'balance_after' => $coin?->balance ?? 0,
            'note' => "Koin {$order->coin_spent} dari order {$order->no_order} hangus permanen (PRD 9.3)",
        ]);
    }

    private function forfeitVoucher(Order $order): void
    {
        // Hanya dari used_at_checkout → hangus. Tidak ada jalur kembali ke active.
        UserVoucher::query()
            ->where('id', $order->user_voucher_id)
            ->where('status', UserVoucher::STATUS_USED_AT_CHECKOUT)
            ->update(['status' => UserVoucher::STATUS_HANGUS]);
    }
}
