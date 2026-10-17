<?php
namespace App\Enums;
enum PaymentMethod: string
{
    case BCA = 'bca';
    case QRIS = 'qris';
    case SHOPEEPAY = 'shopeepay';
    public function label(): string
    {
        return match ($this) {
            self::BCA => 'Bank BCA',
            self::QRIS => 'QRIS',
            self::SHOPEEPAY => 'ShopeePay',
        };
    }
    public function isQr(): bool
    {
        return in_array($this, [self::QRIS, self::SHOPEEPAY]);
    }
}
