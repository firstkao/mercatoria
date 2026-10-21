<?php

namespace App\Enums;

enum PaymentScheme: string
{
    case FP = 'fp';
    case DP = 'dp';

    public function label(): string
    {
        return match ($this) {
            self::FP => 'Full Payment (100%)',
            self::DP => 'Down Payment (50%)',
        };
    }
}
