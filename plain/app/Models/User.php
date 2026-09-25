<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'full_name',
    'birth_date',
    'province',
    'city',
    'district',
    'postal_code',
    'street_address',
    'whatsapp',
    'email',
    'password',
    'parental_consent',
    'tnc_accepted_at',
    'privacy_accepted_at',
    'registered_at',
    'expires_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'view_quota_used' => 'integer',
            'parental_consent' => 'boolean',
            'tnc_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'registered_at' => 'datetime',
            'expires_at' => 'datetime',
            'became_customer_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    /**
     * Get the name shown in admin screens, including for anonymized customers.
     */
    public function displayName(): string
    {
        return $this->anonymized_at ? "Customer terhapus #{$this->id}" : (string) $this->full_name;
    }

    /**
     * Determine whether the user has not yet had a payment verified.
     */
    public function isSpammer(): bool
    {
        return $this->role === UserRole::Spammer;
    }

    /**
     * Get how many product detail views the spammer has left.
     */
    public function remainingViewQuota(): int
    {
        return max(0, Setting::integer('view_quota', 10) - $this->view_quota_used);
    }
    /**
     * Use one product view from the spammer's quota; returns false once the quota is used up.
     */
    public function consumeViewQuota(Product $product): bool
    {
        // A conditional update keeps parallel tabs from exceeding the quota.
        $consumed = static::query()
            ->whereKey($this->getKey())
            ->where('view_quota_used', '<', Setting::integer('view_quota', 10))
            ->increment('view_quota_used');

        if ($consumed === 0) {
            return false;
        }

        $this->view_quota_used++;
        DB::table('product_views')->insert(['user_id' => $this->getKey(), 'product_id' => $product->getKey(), 'viewed_at' => now()]);

        return true;
    }

    public function hasCartItems(): bool
    {
        return DB::table('cart_items')->where('user_id', $this->getKey())->exists();
    }
}