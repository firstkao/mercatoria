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
            self::EXPIRED => 'Koin Kadaluarsa',
        };
    }
}
