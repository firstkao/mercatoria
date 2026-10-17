<?php

namespace App\Enums;

enum CoinSource: string
{
    case REFERRER_REWARD = 'referrer_reward';
    case REFEREE_REWARD = 'referee_reward';
    case ORDER_COMPLETED = 'order_completed';
    case SHARE = 'share';
    case BIRTHDAY = 'birthday';
    case ADMIN_GIFT = 'admin_gift';
    case USED_AT_CHECKOUT = 'used_at_checkout';
    case CHECKOUT_RELEASE = 'checkout_release';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::REFERRER_REWARD => 'Bonus Undang Teman',
            self::REFEREE_REWARD => 'Bonus Daftar via Link',
            self::ORDER_COMPLETED => 'Order Selesai',
            self::SHARE => 'Share Produk',
            self::BIRTHDAY => 'Bonus Ulang Tahun',
            self::ADMIN_GIFT => 'Hadiah dari Admin',
            self::USED_AT_CHECKOUT => 'Pakai Koin di Checkout',
            self::CHECKOUT_RELEASE => 'Pengembalian Koin Pesanan Gagal',
            self::EXPIRED => 'Koin Kadaluarsa',
        };
    }
}
