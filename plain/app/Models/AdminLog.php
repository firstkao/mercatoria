<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable(['admin_id', 'action', 'subject_type', 'subject_id', 'metadata'])]
class AdminLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Record an action taken by the signed-in admin.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function record(string $action, ?Model $subject = null, ?array $metadata = null): self
    {
        return static::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
        ]);
    }

    public const LABELS = [
        'login' => 'Masuk dashboard',
        'create_product' => 'Tambah produk',
        'update_product' => 'Ubah produk',
        'delete_product' => 'Hapus produk',
        'update_pricing' => 'Ubah harga & kurs',
        'update_tiers' => 'Ubah tier ongkir',
        'update_marketplaces' => 'Ubah biaya marketplace',
        'update_display' => 'Ubah tampilan toko',
        'reset_quota' => 'Reset kuota lihat',
        'anonymize_customer' => 'Hapus data customer',
        'reset_identity' => 'Reset blokir',
        'block_identity' => 'Blokir',
        'add_blacklist_name' => 'Tambah nama terlarang',
        'delete_blacklist_name' => 'Hapus nama terlarang',
        'create_page' => 'Tambah halaman',
        'update_page' => 'Ubah halaman',
        'delete_page' => 'Hapus halaman',
        'approve_payment' => 'Setujui bukti pembayaran',
        'reject_payment' => 'Tolak bukti pembayaran',
        // TAMBAHAN BATCH 7
        'create_payment_method' => 'Tambah metode bayar',
        'update_payment_method' => 'Ubah metode bayar',
        'delete_payment_method' => 'Hapus metode bayar',
        'create_hero_slide' => 'Tambah hero slide',
        'update_hero_slide' => 'Ubah hero slide',
        'delete_hero_slide' => 'Hapus hero slide',
        'update_order_status' => 'Ubah status pesanan',
        'verify_payment_accept' => 'Terima pembayaran',
        'verify_payment_reject' => 'Tolak pembayaran',
        'update_profile' => 'Ubah profil admin',
        'update_password' => 'Ganti kata sandi admin',
        'update_general' => 'Ubah pengaturan umum',
        'update_seo' => 'Ubah pengaturan SEO',
        'create_voucher' => 'Tambah voucher',
        'update_voucher' => 'Ubah voucher',
        'delete_voucher' => 'Hapus voucher',
        'backup_database' => 'Backup database',
        'cleanup_storage' => 'Cleanup storage',
        'download_backup' => 'Download backup',
        'delete_backup' => 'Hapus backup',
    ];

    public function label(): string
    {
        return self::LABELS[$this->action] ?? $this->action;
    }

    /**
     * @return array<string, string>
     */
    public function details(): array
    {
        return collect($this->metadata ?? [])
            ->reject(fn ($value) => is_array($value) || $value === null || $value === '')
            ->mapWithKeys(fn ($value, $key) => [str_replace('_', ' ', (string) $key) => (string) $value])
            ->all();
    }
}
