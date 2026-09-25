<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An email address or WhatsApp number that outlives deleted spammer accounts.
 */
#[Fillable(['kind', 'value'])]
class IdentityRecord extends Model
{
    public const KIND_EMAIL = 'email';

    public const KIND_WHATSAPP = 'whatsapp';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deletion_count' => 'integer',
            'blocked_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<RegistrationEvent, $this>
     */
    public function registrationEvents(): HasMany
    {
        return $this->hasMany(RegistrationEvent::class);
    }

    /**
     * Determine whether any of the given email or WhatsApp values is blocked.
     */
    public static function anyBlocked(?string $email, ?string $whatsapp): bool
    {
        $limit = Setting::integer('registration_limit', 3);

        return static::query()
            ->where(function ($query) use ($email, $whatsapp): void {
                $query->where(fn ($q) => $q->where('kind', self::KIND_EMAIL)->where('value', $email))
                    ->orWhere(fn ($q) => $q->where('kind', self::KIND_WHATSAPP)->where('value', $whatsapp));
            })
            ->where(fn ($query) => $query->whereNotNull('blocked_at')->orWhere('deletion_count', '>=', $limit))
            ->exists();
    }
}