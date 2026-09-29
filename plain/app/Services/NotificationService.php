<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    /**
     * Kirim notifikasi ke user.
     */
    public static function send(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $url = null,
        string $icon = 'bell',
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => $icon,
        ]);
    }

    /**
     * Shortcut: notifikasi perubahan status order.
     */
    public static function orderStatus(User $user, Order $order, string $statusLabel, string $body): UserNotification
    {
        return self::send(
            $user,
            'order_status',
            "Pesanan #{$order->order_number}: {$statusLabel}",
            $body,
            route('account.orders.show', $order->order_number),
            'box',
        );
    }

    /**
     * Shortcut: notifikasi koin masuk.
     */
    public static function coinsEarned(User $user, int $amount, string $source): UserNotification
    {
        return self::send(
            $user,
            'coins',
            "Kamu dapat {$amount} koin!",
            "Sumber: " . ucfirst(str_replace('_', ' ', $source)) . ". Cek di halaman Koin Saya.",
            route('account.coins.index'),
            'wallet',
        );
    }

    /**
     * Shortcut: notifikasi bukti pembayaran.
     */
    public static function payment(User $user, Order $order, string $action, ?string $reason = null): UserNotification
    {
        if ($action === 'approved') {
            return self::send(
                $user,
                'payment',
                'Pembayaran Diterima ✓',
                "Pembayaran untuk pesanan #{$order->order_number} sudah diverifikasi.",
                route('account.orders.show', $order->order_number),
                'wallet',
            );
        }

        return self::send(
            $user,
            'payment',
            'Pembayaran Ditolak',
            "Bukti pembayaran pesanan #{$order->order_number} ditolak. Alasan: " . ($reason ?? '—'),
            route('account.orders.show', $order->order_number),
            'wallet',
        );
    }

    /**
     * Notifikasi saat user dapat reward dari referral.
     */
    public static function referralRewarded(User $user, int $amount, string $role): UserNotification
    {
        $title = $role === 'referrer'
            ? "🎁 Bonus referral: {$amount} koin!"
            : "🎁 Selamat! Kamu dapat {$amount} koin dari kode undangan";

        $body = $role === 'referrer'
            ? 'Terima kasih sudah mengundang teman. Koin sudah masuk ke akunmu.'
            : 'Bonus pakai kode undangan sudah masuk. Cek di halaman Koin Saya.';

        return self::send(
            $user,
            'referral',
            $title,
            $body,
            route('account.coins.index'),
            'wallet',
        );
    }

    /**
     * Notifikasi cart abandonment (belum checkout).
     */
    public static function cartAbandoned(User $user, int $itemCount, int $reminderNumber = 1): UserNotification
    {
        $title = $reminderNumber > 1
            ? '⏰ Keranjangmu masih menunggu'
            : '🛒 Keranjangmu belum di-checkout';

        $body = $reminderNumber > 1
            ? 'Produk di keranjangmu bisa keburu diambil orang lain. Yuk selesaikan checkout sekarang.'
            : "Kamu meninggalkan {$itemCount} produk di keranjang. Lanjut checkout?";

        return self::send(
            $user,
            'cart_abandoned',
            $title,
            $body,
            route('cart.index'),
            'box',
        );
    }
}