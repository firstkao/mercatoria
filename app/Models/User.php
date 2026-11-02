<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_CRUSH = 'crush';

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tgl_lahir',
        'no_hp',
        'provinsi',
        'provinsi_id',
        'kota',
        'kota_id',
        'kecamatan',
        'kecamatan_id',
        'alamat',
        'kode_pos',
        'referral_code',
        'referred_by',
        'has_seen_tour',
        'is_migrated',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tgl_lahir' => 'date',
            'has_seen_tour' => 'boolean',
            'is_migrated' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->referral_code === null || $user->referral_code === '') {
                $user->referral_code = generate_referral_code();
            }
        });
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Semua akun back-office (admin + super_admin).
     *
     * @param  Builder<User>  $query
     */
    public function scopeAdmins(Builder $query): void
    {
        $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function isMigrated(): bool
    {
        return $this->is_migrated === true;
    }

    /**
     * PRD 7.21: modal edukasi 1x seumur hidup — setelah verifikasi email pertama.
     */
    public function shouldSeeTour(): bool
    {
        return $this->hasVerifiedEmail() && ! $this->has_seen_tour;
    }

    public function hasCompletedProfile(): bool
    {
        return $this->tgl_lahir !== null;
    }

    /**
     * PRD 48.4 / Skema DB 16.2: gate sebelum Buat Pesanan & Konfirmasi Pembayaran.
     */
    public function canOrderOrConfirmPayment(): bool
    {
        return $this->hasVerifiedEmail() && $this->hasCompletedProfile();
    }

    public function crush(): HasOne
    {
        return $this->hasOne(Crush::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
