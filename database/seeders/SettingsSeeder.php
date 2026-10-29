<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Default pengaturan sesuai PRD Section 23 (+ AMANDEMEN & Skema DB).
     *
     * @var array<int, array{key: string, value: string, type: string, group: string, description: string}>
     */
    private const SETTINGS = [
        // Pricing
        ['key' => 'rate_rmb_idr', 'value' => '3000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Rate RMB → IDR. Default 3.000.'],
        ['key' => 'profit_percent', 'value' => '11', 'type' => 'int', 'group' => 'pricing', 'description' => 'Profit margin (persen).'],
        ['key' => 'ongkir_cn_btm_per_kg', 'value' => '25000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Ongkir CN → Batam per kg (IDR).'],
        ['key' => 'ongkir_btm_jkt_per_kg', 'value' => '60000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Ongkir Batam → Jakarta per kg (IDR).'],
        ['key' => 'default_weight_gram', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'Default berat produk (gram). 0 = admin wajib input.'],
        ['key' => 'price_rounding', 'value' => '5000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Pembulatan harga ke kelipatan (hanya di harga item/varian).'],
        ['key' => 'fee_toco_fp', 'value' => '1', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Toco Full Payment (IDR).'],
        ['key' => 'fee_toco_dp', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Toco Down Payment (IDR).'],
        ['key' => 'fee_shopee_fp', 'value' => '109', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Shopee Full Payment (IDR).'],
        ['key' => 'fee_shopee_dp', 'value' => '25', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Shopee Down Payment (persen pelunasan).'],
        ['key' => 'fee_qris_percent', 'value' => '0.03', 'type' => 'string', 'group' => 'pricing', 'description' => 'Fee QRIS (persen).'],
        ['key' => 'qris_threshold', 'value' => '500000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Threshold QRIS (IDR); di bawah ini tanpa fee.'],
        ['key' => 'dp_minimum_percent', 'value' => '50', 'type' => 'int', 'group' => 'pricing', 'description' => 'DP minimum (persen).'],
        ['key' => 'auto_cancel_dp_days', 'value' => '7', 'type' => 'int', 'group' => 'pricing', 'description' => 'Auto-cancel DP (hari setelah Sampai WH Indonesia).'],
        ['key' => 'payment_deadline_days', 'value' => '7', 'type' => 'int', 'group' => 'pricing', 'description' => 'Timer 7 hari (Menunggu Pelunasan).'],

        // Coin & voucher
        ['key' => 'coin_referral_referrer', 'value' => '3000', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin untuk referrer saat referee selesai order pertama.'],
        ['key' => 'coin_referral_referee', 'value' => '1000', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin untuk referee saat pembayaran pertama terverifikasi.'],
        ['key' => 'coin_order_percent', 'value' => '1', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin dari order selesai (persen dari total transaksi).'],
        ['key' => 'coin_share', 'value' => '5', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin share link produk per IP unik.'],
        ['key' => 'coin_max_discount_percent', 'value' => '5', 'type' => 'int', 'group' => 'coin', 'description' => 'Maksimal diskon koin per transaksi (persen).'],
        ['key' => 'coin_expiry_days', 'value' => '365', 'type' => 'int', 'group' => 'coin', 'description' => 'Masa berlaku koin (hari).'],
        ['key' => 'coin_birthday_bonus', 'value' => '5000', 'type' => 'int', 'group' => 'coin', 'description' => 'Bonus koin ulang tahun.'],
        ['key' => 'voucher_birthday_nominal', 'value' => '25000', 'type' => 'int', 'group' => 'coin', 'description' => 'Nominal voucher ulang tahun (IDR).'],
        ['key' => 'voucher_birthday_min_belanja', 'value' => '100000', 'type' => 'int', 'group' => 'coin', 'description' => 'Minimum belanja voucher ulang tahun (IDR).'],

        // User state & security
        ['key' => 'auto_delete_crush_days', 'value' => '30', 'type' => 'int', 'group' => 'user', 'description' => 'Auto-delete akun Crush tanpa transaksi (hari).'],
        ['key' => 'view_limit', 'value' => '10', 'type' => 'int', 'group' => 'user', 'description' => 'View limit detail produk untuk Crush/Ex/ONS.'],
        ['key' => 'max_register_without_purchase', 'value' => '3', 'type' => 'int', 'group' => 'user', 'description' => 'Maksimal pendaftaran dengan email/HP sama tanpa belanja.'],
        ['key' => 'email_verification_hours', 'value' => '24', 'type' => 'int', 'group' => 'user', 'description' => 'Masa berlaku link verifikasi email (jam).'],
        ['key' => 'login_rate_limit_per_minute', 'value' => '5', 'type' => 'int', 'group' => 'security', 'description' => 'Rate limit percobaan login (per menit).'],
        ['key' => 'login_lockout_minutes', 'value' => '15', 'type' => 'int', 'group' => 'security', 'description' => 'Durasi lockout setelah 5 kali gagal login (menit).'],
        ['key' => 'cart_reminder_minutes', 'value' => '30', 'type' => 'int', 'group' => 'user', 'description' => 'Interval pengingat keranjang (menit).'],
        ['key' => 'unblock_grace_days', 'value' => '7', 'type' => 'int', 'group' => 'user', 'description' => 'Grace period setelah unblock (hari).'],
        ['key' => 'state_threshold_ex_days', 'value' => '30', 'type' => 'int', 'group' => 'user', 'description' => 'Ambang transisi ke Ex (hari sejak pesanan terakhir keputus).'],
        ['key' => 'state_threshold_ons_days', 'value' => '60', 'type' => 'int', 'group' => 'user', 'description' => 'Ambang transisi ke Blocked (hari sejak pesanan terakhir keputus).'],
        ['key' => 'state_notify_h7_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'user', 'description' => 'Notifikasi H-7 sebelum state berubah.'],
        ['key' => 'forever_delete_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Aksi Forever Delete untuk user di spammer list.'],
        ['key' => 'inject_spammer_max_attempt', 'value' => '3', 'type' => 'int', 'group' => 'security', 'description' => 'Maksimal percobaan inject data spammer.'],
        ['key' => 'bot_whitelist_search', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Whitelist bot search engine.'],
        ['key' => 'bot_whitelist_ai', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Whitelist bot AI.'],
        ['key' => 'bot_whitelist_social', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Whitelist bot social preview.'],
        ['key' => 'share_restrict_id_only', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Batas koin share produk hanya untuk IP Indonesia.'],

        // Tier
        ['key' => 'tier_silver_threshold', 'value' => '0', 'type' => 'int', 'group' => 'tier', 'description' => 'Batas total belanja MVP Silver.'],
        ['key' => 'tier_gold_threshold', 'value' => '2000000', 'type' => 'int', 'group' => 'tier', 'description' => 'Batas total belanja MVP Gold.'],
        ['key' => 'tier_platinum_threshold', 'value' => '5000000', 'type' => 'int', 'group' => 'tier', 'description' => 'Batas total belanja MVP Platinum.'],
        ['key' => 'tier_trigger_status', 'value' => 'Sedang Diproses', 'type' => 'string', 'group' => 'tier', 'description' => 'Trigger progress Tier (status order).'],

        // Late penalty / split box
        ['key' => 'late_penalty_per_day', 'value' => '1000', 'type' => 'int', 'group' => 'penalty', 'description' => 'Denda keterlambatan CO per hari (flat, IDR).'],
        ['key' => 'late_penalty_pay_hours', 'value' => '24', 'type' => 'int', 'group' => 'penalty', 'description' => 'Batas bayar denda setelah Buka Kunci (jam).'],
        ['key' => 'late_penalty_max_days', 'value' => '0', 'type' => 'int', 'group' => 'penalty', 'description' => 'Batas hari denda; 0 = nonaktif (Hangus Permanen manual).'],
        ['key' => 'late_penalty_pickup_days', 'value' => '7', 'type' => 'int', 'group' => 'penalty', 'description' => 'Timer CO setelah denda lunas (hari).'],
        ['key' => 'max_auto_batches', 'value' => '3', 'type' => 'int', 'group' => 'splitbox', 'description' => 'Maks kloter otomatis per Split Box.'],

        // Email & notification
        ['key' => 'pause_customer_emails', 'value' => '0', 'type' => 'bool', 'group' => 'email', 'description' => 'Stop kirim semua email ke customer saat maintenance/testing (1 = Stop, 0 = Normal). OTP Admin tetap terkirim.'],
        ['key' => 'admin_notify_payment_upload', 'value' => '1', 'type' => 'bool', 'group' => 'email', 'description' => 'Email notif admin saat bukti bayar masuk.'],
        ['key' => 'note_to_customer_max_chars', 'value' => '1000', 'type' => 'int', 'group' => 'email', 'description' => 'Maksimal karakter Note to Customer.'],
        ['key' => 'note_to_customer_send_email', 'value' => '0', 'type' => 'bool', 'group' => 'email', 'description' => 'Kirim email juga untuk Note to Customer (default hanya bell).'],
        ['key' => 'bell_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'notification', 'description' => 'Bell Notifikasi User aktif.'],
        ['key' => 'bell_polling_seconds', 'value' => '60', 'type' => 'int', 'group' => 'notification', 'description' => 'Interval polling bell (detik).'],
        ['key' => 'bell_dropdown_limit', 'value' => '10', 'type' => 'int', 'group' => 'notification', 'description' => 'Maksimal item dropdown bell.'],
        ['key' => 'bell_retention_days', 'value' => '90', 'type' => 'int', 'group' => 'notification', 'description' => 'Retensi notifikasi bell (hari).'],
        ['key' => 'bell_auto_delete_read', 'value' => '0', 'type' => 'bool', 'group' => 'notification', 'description' => 'Auto-delete notifikasi setelah dibaca.'],
        ['key' => 'bell_toast_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'notification', 'description' => 'Toast in-web untuk notifikasi.'],
        ['key' => 'bell_per_page', 'value' => '20', 'type' => 'int', 'group' => 'notification', 'description' => 'Jumlah notifikasi per halaman.'],

        // Image protection & media
        ['key' => 'image_protection_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Blokir klik-kanan save, drag, & long-press khusus pada gambar (Copy-paste teks tetap bebas).'],
        ['key' => 'image_watermark_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'security', 'description' => 'Tanamkan watermark MERCATORIA.ID otomatis pada foto produk, varian, dan deskripsi panjang.'],
        ['key' => 'image_watermark_opacity', 'value' => '25', 'type' => 'int', 'group' => 'security', 'description' => 'Tingkat ketebalan watermark foto (10 - 80%). Default 25%.'],
        ['key' => 'snapshot_image_retention', 'value' => 'permanent', 'type' => 'string', 'group' => 'snapshot', 'description' => 'Retensi gambar snapshot order.'],
        ['key' => 'snapshot_image_path_prefix', 'value' => '/storage/order-snapshots/', 'type' => 'string', 'group' => 'snapshot', 'description' => 'Prefix path gambar snapshot.'],

        // Order & product
        ['key' => 'auto_delete_payment_proof', 'value' => '1', 'type' => 'bool', 'group' => 'order', 'description' => 'Auto-delete file bukti transfer (Selesai/Refunded/Hangus).'],
        ['key' => 'customer_note_max_chars', 'value' => '500', 'type' => 'int', 'group' => 'order', 'description' => 'Maksimal karakter Catatan Pembelian.'],
        ['key' => 'invoice_prefix', 'value' => 'INV-MER', 'type' => 'string', 'group' => 'order', 'description' => 'Prefix nomor invoice.'],
        ['key' => 'product_delete_confirm_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'product', 'description' => 'Konfirmasi warning count saat hapus produk.'],
        ['key' => 'product_bulk_delete_enabled', 'value' => '1', 'type' => 'bool', 'group' => 'product', 'description' => 'Bulk delete produk aktif.'],

        // Display & SEO
        ['key' => 'display_timezone', 'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'display', 'description' => 'Timezone tampilan (WIB / GMT+7).'],
        ['key' => 'catalog_per_page', 'value' => '30', 'type' => 'int', 'group' => 'display', 'description' => 'Pagination katalog per halaman.'],
        ['key' => 'related_products_count', 'value' => '6', 'type' => 'int', 'group' => 'display', 'description' => 'Jumlah produk terkait.'],
        ['key' => 'best_offers_count', 'value' => '5', 'type' => 'int', 'group' => 'display', 'description' => 'Jumlah Penawaran Terbaik.'],
        ['key' => 'recently_viewed_count', 'value' => '5', 'type' => 'int', 'group' => 'display', 'description' => 'Jumlah Terakhir Dilihat.'],
        ['key' => 'ga4_measurement_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'description' => 'GA4 Measurement ID (format G-XXXXXXXXXX).'],
        ['key' => 'log_retention_days', 'value' => '30', 'type' => 'int', 'group' => 'security', 'description' => 'Retensi activity / auth / admin log (hari).'],

        // URL eksternal (sosial & marketplace)
        ['key' => 'url_toco', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link marketplace Toco.'],
        ['key' => 'url_shopee', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link marketplace Shopee.'],
        ['key' => 'url_instagram', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link Instagram.'],
        ['key' => 'url_facebook', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link Facebook.'],
        ['key' => 'url_x', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link X (Twitter).'],
        ['key' => 'url_threads', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link Threads.'],
        ['key' => 'url_whatsapp', 'value' => '#', 'type' => 'string', 'group' => 'social', 'description' => 'Link WhatsApp.'],

        // Promo bar (PRD 40.1)
        ['key' => 'promo_bar_text', 'value' => 'Gunakan kode promo NEWWEB dan nikmati potongan harga untuk belanja pertamamu.', 'type' => 'string', 'group' => 'marketing', 'description' => 'Teks promo bar di atas halaman.'],
        ['key' => 'promo_bar_link', 'value' => '', 'type' => 'string', 'group' => 'marketing', 'description' => 'Link tujuan promo bar (opsional).'],
        ['key' => 'promo_bar_active', 'value' => '1', 'type' => 'bool', 'group' => 'marketing', 'description' => 'Tampilkan promo bar (1 = aktif, 0 = nonaktif).'],
    ];

    public function run(): void
    {
        foreach (self::SETTINGS as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
