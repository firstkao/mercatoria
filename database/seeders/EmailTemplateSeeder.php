<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['verify_email', 'Verifikasi Email', 'security@mercatoria.id', 'Verifikasi Email Kamu — Mercatoria'],
            ['reset_password', 'Reset Password', 'security@mercatoria.id', 'Reset Password Mercatoria'],
            ['admin_otp', 'OTP Admin', 'security@mercatoria.id', 'Kode OTP Admin — Mercatoria'],
            ['spammer_blocked', 'Akun Diblokir', 'security@mercatoria.id', 'Akun Kamu Diblokir'],
            ['crush_deleted', 'Akun Crush Dihapus', 'security@mercatoria.id', 'Akun Kamu Telah Dihapus'],
            ['welcome', 'Selamat Datang', 'info@mercatoria.id', 'Selamat Datang di Mercatoria!'],
            ['order_created', 'Order Dibuat', 'info@mercatoria.id', 'Pesanan Kamu Berhasil Dibuat'],
            ['payment_verified', 'Pembayaran Terverifikasi', 'info@mercatoria.id', 'Pembayaran Kamu Terverifikasi'],
            ['payment_rejected', 'Pembayaran Ditolak', 'info@mercatoria.id', 'Bukti Bayar Kamu Ditolak'],
            ['order_status_changed', 'Status Order Berubah', 'info@mercatoria.id', 'Update Status Pesanan Kamu'],
            ['order_completed', 'Order Selesai', 'info@mercatoria.id', 'Pesanan Kamu Selesai 🎉'],
            ['settlement_reminder', 'Reminder Pelunasan', 'info@mercatoria.id', 'Waktunya Lunasi Pesanan Kamu'],
            ['order_hangus', 'Order Hangus', 'info@mercatoria.id', 'Pesanan Kamu Hangus'],
            ['cart_reminder', 'Reminder Cart', 'info@mercatoria.id', 'Item di Keranjangmu Nungguin!'],
            ['coin_earned', 'Koin Bertambah', 'info@mercatoria.id', 'Koin Kamu Bertambah 🪙'],
            ['coin_expiring', 'Koin Akan Expired', 'info@mercatoria.id', 'Koin Kamu Akan Kadaluarsa'],
            ['voucher_received', 'Voucher Diterima', 'info@mercatoria.id', 'Voucher Baru Untukmu 🎟️'],
            ['voucher_expiring', 'Voucher Akan Expired', 'info@mercatoria.id', 'Voucher Kamu Akan Kadaluarsa'],
            ['birthday_reward', 'Bonus Ulang Tahun', 'info@mercatoria.id', 'Selamat Ulang Tahun! 🎂'],
            ['referral_reward', 'Bonus Referral', 'info@mercatoria.id', 'Bonus Referral Kamu Masuk 🎁'],
            ['referee_reward', 'Bonus Referee', 'info@mercatoria.id', 'Kamu Dapat Bonus dari Link Teman!'],
            ['state_warning_h7', 'Peringatan H-7 State', 'info@mercatoria.id', 'Akun Kamu Akan Berubah State'],
            ['state_changed', 'State Berubah', 'info@mercatoria.id', 'State Akun Kamu Berubah'],
            ['cs_reply', 'Balasan CS', 'cs@mercatoria.id', 'CS Mercatoria Membalas Pesanmu'],
            ['cs_followup', 'Follow-up Pesanan', 'cs@mercatoria.id', 'Follow-up Pesanan Kamu'],
            ['admin_payment_notification', 'Notif Admin - Pembayaran Masuk', 'cs@mercatoria.id', 'Pembayaran Baru Masuk'],
            ['user_gift_notification', 'Gift dari Admin', 'info@mercatoria.id', 'Kamu Dapat Hadiah dari Mercatoria!'],
        ];

        foreach ($templates as [$code, $name, $from, $subject]) {
            EmailTemplate::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'from_email' => $from,
                    'subject' => $subject,
                    'body' => '<p>Halo {{nama_user}},</p><p>Ini adalah template ' . $name . '. Silakan edit di admin panel.</p>',
                    'variables' => ['nama_user', 'email', 'no_order', 'link', 'tanggal', 'nama_toko'],
                    'is_active' => true,
                ]
            );
        }
    }
}
