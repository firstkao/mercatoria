<?php

namespace App\Models;

use App\Enums\UserState;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'no_hp', 'tgl_lahir',
        'provinsi', 'kota', 'kecamatan', 'alamat_lengkap', 'kode_pos',
        'role', 'referral_code', 'invited_by_code',
        'has_seen_edu_modal', 'has_seen_tour', 'is_migrated',
        'last_login', 'admin_otp', 'admin_otp_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'admin_otp'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'tgl_lahir' => 'date',
        'last_login' => 'datetime',
        'admin_otp_expires_at' => 'datetime',
        'has_seen_edu_modal' => 'boolean',
        'has_seen_tour' => 'boolean',
        'is_migrated' => 'boolean',
        'password' => 'hashed',
    ];

    // ─── Relationships ─────────────────────────
    public function crush(): HasOne
    {
        return $this->hasOne(Crush::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function coin(): HasOne
    {
        return $this->hasOne(Coin::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function productViews(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(UserGift::class);
    }

    // ─── Helpers: Role & State ─────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCrush(): bool
    {
        return $this->crush()->exists();
    }

    public function isCustomer(): bool
    {
        return $this->customer()->exists();
    }

    public function currentState(): UserState
    {
        if ($this->isAdmin()) {
            return UserState::MVP;
        }
        if ($this->isCrush()) {
            return UserState::CRUSH;
        }

        return UserState::from($this->customer?->current_state ?? 'crush');
    }

    public function currentTier(): ?string
    {
        return $this->customer?->tier;
    }

    public function getCurrentBadgeAttribute(): UserState
    {
        return $this->currentState();
    }

    // ─── Helpers: Migrasi (v1.5) ───────────────
    public function isMigrated(): bool
    {
        return $this->is_migrated === true;
    }

    public function hasCompletedProfile(): bool
    {
        return $this->tgl_lahir !== null;
    }
}
