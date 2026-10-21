<?php

namespace App\Enums;

enum PaymentType: string
{
    case DP = 'dp';
    case FULL = 'full';
    case SETTLEMENT = 'settlement';
    case REFUND = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::DP => 'DP',
            self::FULL => 'Full',
            self::SETTLEMENT => 'Pelunasan',
            self::REFUND => 'Refund',
        };
    }
}
