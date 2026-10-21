<?php

namespace App\Enums;

enum RefundStatus: string
{
    case REQUESTED = 'requested';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Diminta',
            self::PROCESSING => 'Diproses',
            self::COMPLETED => 'Selesai',
            self::REJECTED => 'Ditolak',
        };
    }
}
