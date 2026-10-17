<?php

namespace App\Enums;

enum TierLevel: string
{
    case SILVER = 'silver';
    case GOLD = 'gold';
    case PLATINUM = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::SILVER => 'MVP Silver',
            self::GOLD => 'MVP Gold',
            self::PLATINUM => 'MVP Platinum',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SILVER => '#B0BEC5',
            self::GOLD => '#FFC107',
            self::PLATINUM => '#4FC3F7',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SILVER => '🥈',
            self::GOLD => '🥇',
            self::PLATINUM => '💎',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::SILVER, self::PLATINUM => '#212121',
            self::GOLD => '#333333',
        };
    }

    public function minSpending(): float
    {
        return match ($this) {
            self::SILVER => 0,
            self::GOLD => 2_000_000,
            self::PLATINUM => 5_000_000,
        };
    }

    public static function fromSpending(float $total): self
    {
        return match (true) {
            $total >= 5_000_000 => self::PLATINUM,
            $total >= 2_000_000 => self::GOLD,
            default => self::SILVER,
        };
    }
}
