<?php

namespace App\Enums;

enum UserRole: string
{
    case Spammer = 'spammer';
    case Customer = 'customer';

    /**
     * Get the label shown to shoppers and the admin.
     */
    public function label(): string
    {
        return match ($this) {
            self::Spammer => 'Spammer',
            self::Customer => 'Customer',
        };
    }
}