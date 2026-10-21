<?php

namespace App\Enums;

enum NotificationType: string
{
    case ORDER = 'order';
    case PAYMENT = 'payment';
    case COIN = 'coin';
    case VOUCHER = 'voucher';
    case STATE = 'state';
    case REFERRAL = 'referral';
    case CART = 'cart';
    case GIFT = 'gift';
    case BIRTHDAY = 'birthday';
    case SYSTEM = 'system';
    case ORDER_NOTE = 'order_note';

    public function icon(): string
    {
        return match ($this) {
            self::ORDER => '📦',
            self::PAYMENT => '💳',
            self::COIN => '🪙',
            self::VOUCHER => '🎟️',
            self::STATE => '⚠️',
            self::REFERRAL => '👥',
            self::CART => '🛒',
            self::GIFT => '🎁',
            self::BIRTHDAY => '🎂',
            self::SYSTEM => 'ℹ️',
            self::ORDER_NOTE => '💬',
        };
    }
}
