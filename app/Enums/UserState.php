<?php

namespace App\Enums;

enum UserState: string
{
    case CRUSH = 'crush';
    case MVP = 'mvp';
    case EX = 'ex';
    case ONS = 'ons';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::CRUSH => 'Crush',
            self::MVP => 'MVP',
            self::EX => 'Ex',
            self::ONS => 'ONS',
            self::BLOCKED => 'Blocked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CRUSH => '#F48FB1',
            self::MVP => '#43A047',
            self::EX => '#64B5F6',
            self::ONS => '#FFB74D',
            self::BLOCKED => '#E53935',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CRUSH => '🌸',
            self::MVP => '⭐',
            self::EX => '💤',
            self::ONS => '🌙',
            self::BLOCKED => '🔒',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::CRUSH, self::EX, self::ONS => '#212121',
            self::MVP, self::BLOCKED => '#FFFFFF',
        };
    }

    public function hasViewLimit(): bool
    {
        return in_array($this, [self::CRUSH, self::EX, self::ONS]);
    }

    public function canOrder(): bool
    {
        return $this !== self::BLOCKED;
    }
}
