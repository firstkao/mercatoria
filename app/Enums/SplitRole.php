<?php

namespace App\Enums;

enum SplitRole: string
{
    case Normal = 'normal';
    case Hot1 = 'hot_1';
    case Hot2 = 'hot_2';

    public function isHot(): bool
    {
        return $this !== self::Normal;
    }

    /**
     * Jumlah Normal yang dibutuhkan sebagai pasangan (PRD 49.2).
     */
    public function pairRequirement(): int
    {
        return match ($this) {
            self::Hot1 => 1,
            self::Hot2 => 2,
            self::Normal => 0,
        };
    }
}
