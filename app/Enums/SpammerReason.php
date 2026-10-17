<?php

namespace App\Enums;

enum SpammerReason: string
{
    case NO_PURCHASE_3X = 'no_purchase_3x';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::NO_PURCHASE_3X => '3 kali daftar tanpa belanja',
            self::MANUAL => 'Blokir manual oleh admin',
        };
    }
}
