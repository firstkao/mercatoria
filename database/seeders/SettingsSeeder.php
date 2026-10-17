<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── GENERAL ───────────────────────────────────
            ['key' => 'store_name', 'value' => 'Mercatoria', 'type' => 'string', 'group' => 'general', 'description' => 'Nama toko'],
            ['key' => 'cs_email', 'value' => 'cs@mercatoria.id', 'type' => 'string', 'group' => 'general', 'description' => 'Email CS'],
            ['key' => 'wa_admin', 'value' => '6281219683709', 'type' => 'string', 'group' => 'general', 'description' => 'WA Admin'],
            ['key' => 'store_address', 'value' => 'DKI Jakarta', 'type' => 'string', 'group' => 'general', 'description' => 'Alamat toko'],

            // ─── PRICING ───────────────────────────────────
            ['key' => 'rate_rmb_idr', 'value' => '2200', 'type' => 'int', 'group' => 'pricing', 'description' => 'Rate RMB → IDR'],
            ['key' => 'profit_percent', 'value' => '11', 'type' => 'decimal', 'group' => 'pricing', 'description' => 'Profit %'],
            ['key' => 'ongkir_cn_btm_per_kg', 'value' => '25000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Ongkir CN → Batam per kg'],
            ['key' => 'ongkir_btm_jkt_per_kg', 'value' => '60000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Ongkir Batam → Jakarta per kg'],
            ['key' => 'default_weight_gram', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'Default berat produk (gram)'],
            ['key' => 'fee_toco_fp', 'value' => '1', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Toco FP'],
            ['key' => 'fee_toco_dp', 'value' => '0', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Toco DP'],
            ['key' => 'fee_shopee_fp', 'value' => '109', 'type' => 'int', 'group' => 'pricing', 'description' => 'Fee Shopee FP'],
            ['key' => 'fee_shopee_dp_percent', 'value' => '25', 'type' => 'decimal', 'group' => 'pricing', 'description' => 'Fee Shopee DP %'],
            ['key' => 'fee_qris_percent', 'value' => '0.03', 'type' => 'decimal', 'group' => 'pricing', 'description' => 'Fee QRIS %'],
            ['key' => 'qris_threshold', 'value' => '500000', 'type' => 'int', 'group' => 'pricing', 'description' => 'Threshold QRIS'],
            ['key' => 'dp_minimum_percent', 'value' => '50', 'type' => 'int', 'group' => 'pricing', 'description' => 'DP minimum %'],

            // ─── COIN ──────────────────────────────────────
            ['key' => 'coin_referrer', 'value' => '3000', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin Referrer'],
            ['key' => 'coin_referee', 'value' => '1000', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin Referee'],
            ['key' => 'coin_order_percent', 'value' => '1', 'type' => 'decimal', 'group' => 'coin', 'description' => 'Koin per order %'],
            ['key' => 'coin_share', 'value' => '5', 'type' => 'int', 'group' => 'coin', 'description' => 'Koin share produk'],
            ['key' => 'coin_max_discount_percent', 'value' => '5', 'type' => 'decimal', 'group' => 'coin', 'description' => 'Max diskon koin %'],
            ['key' => 'coin_expiry_days', 'value' => '365', 'type' => 'int', 'group' => 'coin', 'description' => 'Expiry koin (hari)'],
            ['key' => 'coin_birthday_bonus', 'value' => '5000', 'type' => 'int', 'group' => 'coin', 'description' => 'Bonus ulang tahun'],

            // ─── STATE ─────────────────────────────────────
            ['key' => 'crush_auto_delete_days', 'value' => '30', 'type' => 'int', 'group' => 'state', 'description' => 'Auto-delete Crush (hari)'],
            ['key' => 'mvp_to_ex_days', 'value' => '30', 'type' => 'int', 'group' => 'state', 'description' => 'MVP → Ex (hari)'],
            ['key' => 'ex_to_ons_days', 'value' => '60', 'type' => 'int', 'group' => 'state', 'description' => 'Ex → ONS (hari)'],
            ['key' => 'pickup_timer_days', 'value' => '7', 'type' => 'int', 'group' => 'state', 'description' => 'Timer pickup (hari)'],
            ['key' => 'view_limit', 'value' => '10', 'type' => 'int', 'group' => 'state', 'description' => 'View limit Crush/Ex/ONS'],

            // ─── SECURITY ──────────────────────────────────
            ['key' => 'login_rate_limit', 'value' => '5', 'type' => 'int', 'group' => 'security', 'description' => 'Login rate limit per menit'],
            ['key' => 'lockout_duration_minutes', 'value' => '15', 'type' => 'int', 'group' => 'security', 'description' => 'Lockout duration (menit)'],
            ['key' => 'spammer_max_attempt', 'value' => '3', 'type' => 'int', 'group' => 'security', 'description' => 'Max attempt spammer'],
            ['key' => 'unblock_grace_days', 'value' => '7', 'type' => 'int', 'group' => 'security', 'description' => 'Grace period unblock (hari)'],

            // ─── NOTIFICATION ──────────────────────────────
            ['key' => 'bell_poll_interval', 'value' => '60', 'type' => 'int', 'group' => 'notification', 'description' => 'Bell polling (detik)'],
            ['key' => 'bell_max_dropdown', 'value' => '10', 'type' => 'int', 'group' => 'notification', 'description' => 'Max dropdown bell'],
            ['key' => 'bell_retention_days', 'value' => '90', 'type' => 'int', 'group' => 'notification', 'description' => 'Retention notifikasi (hari)'],
            ['key' => 'bell_toast_duration', 'value' => '5', 'type' => 'int', 'group' => 'notification', 'description' => 'Durasi toast (detik)'],
            ['key' => 'note_to_customer_max_chars', 'value' => '1000', 'type' => 'int', 'group' => 'notification', 'description' => 'Max karakter note to customer'],
            ['key' => 'customer_note_max_chars', 'value' => '500', 'type' => 'int', 'group' => 'notification', 'description' => 'Max karakter customer note'],

            // ─── SNAPSHOT ──────────────────────────────────
            ['key' => 'snapshot_image_path', 'value' => 'order-snapshots', 'type' => 'string', 'group' => 'snapshot', 'description' => 'Path snapshot image'],
            ['key' => 'snapshot_retention', 'value' => 'permanent', 'type' => 'string', 'group' => 'snapshot', 'description' => 'Retention snapshot'],
            ['key' => 'delete_product_confirm_text', 'value' => 'HAPUS', 'type' => 'string', 'group' => 'snapshot', 'description' => 'Teks konfirmasi hapus produk'],

            // ─── SEO & SHARE ───────────────────────────────
            ['key' => 'ga4_measurement_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'description' => 'GA4 Measurement ID'],
            ['key' => 'share_restrict_to_indonesia', 'value' => '1', 'type' => 'bool', 'group' => 'seo', 'description' => 'Batasi share reward ke IP Indonesia'],

            // ─── PAGE ──────────────────────────────────────
            ['key' => 'pagination_per_page', 'value' => '30', 'type' => 'int', 'group' => 'page', 'description' => 'Pagination per halaman'],
            ['key' => 'related_products_count', 'value' => '6', 'type' => 'int', 'group' => 'page', 'description' => 'Jumlah produk terkait'],
            ['key' => 'best_offer_count', 'value' => '5', 'type' => 'int', 'group' => 'page', 'description' => 'Jumlah penawaran terbaik'],
            ['key' => 'recently_viewed_count', 'value' => '5', 'type' => 'int', 'group' => 'page', 'description' => 'Jumlah terakhir dilihat'],
            ['key' => 'invoice_prefix', 'value' => 'INV-MER', 'type' => 'string', 'group' => 'page', 'description' => 'Invoice prefix'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}