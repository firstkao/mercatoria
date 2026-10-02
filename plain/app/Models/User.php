<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    'email_verified_at',
    'email_otp',
    'email_otp_expires_at',
    'email_otp_attempts',
    'password',
    'referral_code',
    'role',
    'view_quota_used',
    'parental_consent',
    'tnc_accepted_at',
    'privacy_accepted_at',
    'registered_at',
    'expires_at',
    'became_customer_at',
    'anonymized_at',
])]
#[Hidden(['password', 'remember_token', 'email_otp'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'email_verified_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'email_otp_attempts' => 'integer',
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

    // ============================================================
    // RELASI
    // ============================================================

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function cartReminders(): HasMany
    {
        return $this->hasMany(CartReminder::class);
    }

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id')->latest();
    }

    public function referralReceived(): HasOne
    {
        return $this->hasOne(Referral::class, 'referee_id');
    }

    // ============================================================
    // EMAIL VERIFICATION (OTP)
    // ============================================================

    public function hasVerifiedEmail(): bool
    {
        if (Setting::get('email_verification_enabled', '1') !== '1') {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    public function generateEmailOtp(): string
    {
        $otp = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_otp' => $otp,
            'email_otp_expires_at' => now()->addMinutes(15),
            'email_otp_attempts' => 0,
        ])->save();

        return $otp;
    }

    public function verifyEmailOtp(string $input): bool
    {
        if (! $this->email_otp) {
            return false;
        }

        if ($this->email_otp_expires_at && $this->email_otp_expires_at->isPast()) {
            return false;
        }

        if ($this->email_otp_attempts >= 5) {
            return false;
        }

        if (! hash_equals($this->email_otp, $input)) {
            $this->increment('email_otp_attempts');
            return false;
        }

        $this->forceFill([
            'email_verified_at' => now(),
            'email_otp' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
        ])->save();

        return true;
    }

    public function sendEmailVerificationOtp(): void
    {
        $otp = $this->generateEmailOtp();
        $this->notify(new \App\Notifications\EmailVerificationOtpNotification($otp));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    // ============================================================
    // REFERRAL
    // ============================================================

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function ensureReferralCode(): string
    {
        if (! $this->referral_code) {
            $this->forceFill(['referral_code' => self::generateReferralCode()])->save();
        }

        return $this->referral_code;
    }

    public function referralUrl(): string
    {
        return url('/daftar?ref=' . $this->ensureReferralCode());
    }

    // ============================================================
    // NOTIFIKASI
    // ============================================================

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    // ============================================================
    // HELPER
    // ============================================================

    public function displayName(): string
    {
        return $this->anonymized_at ? "Customer terhapus #{$this->id}" : (string) $this->full_name;
    }

    public function isSpammer(): bool
    {
        return $this->role === UserRole::Spammer;
    }

    /**
     * Helper STRICT MODE (permintaan user): spammer dengan kuota habis tidak
     * boleh mengakses katalog & detail produk sama sekali — hanya Home & Akun.
     * Dibungkus try/catch supaya DB lama tanpa kolom view_quota_used tetap aman.
     */
    public function hasExhaustedQuota(): bool
    {
        try {
            return $this->remainingViewQuota() <= 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * // Status hanya berubah jadi customer jika ada pembelian terverifikasi.
     * Satu order "selesai" ATAU satu bukti pembayaran berstatus diterima
     * dianggap pembelian terverifikasi.
     */
    public function hasVerifiedPurchase(): bool
    {
        try {
            return \App\Models\Order::query()
                ->where('user_id', $this->getKey())
                ->whereIn('status', [
                    \App\Enums\OrderStatus::Selesai->value,
                    \App\Enums\OrderStatus::PembayaranDiterima->value,
                ])
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function remainingViewQuota(): int
    {
        return max(0, Setting::integer('view_quota', 10) - $this->view_quota_used);
    }

    public function consumeViewQuota(Product $product): bool
    {
        $consumed = static::query()
            ->whereKey($this->getKey())
            ->where('view_quota_used', '<', Setting::integer('view_quota', 10))
            ->increment('view_quota_used');

        if ($consumed === 0) {
            return false;
        }

        $this->view_quota_used++;
        DB::table('product_views')->insert([
            'user_id' => $this->getKey(),
            'product_id' => $product->getKey(),
            'viewed_at' => now(),
        ]);

        return true;
    }

    public function hasCartItems(): bool
    {
        return DB::table('cart_items')->where('user_id', $this->getKey())->exists();
    }

    public function whatsappLink(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->whatsapp);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}