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
            $body = match ($code) {
                'admin_otp' => '<p>Halo {{nama_user}},</p><p>Kode OTP admin kamu: <strong>{{otp}}</strong>.</p><p>Kode ini berlaku selama 5 menit. Jangan bagikan kepada siapa pun.</p>',
                'cart_reminder' => '<p>Halo {{nama_user}},</p><p>Masih ada item menunggu di keranjangmu. Lanjutkan belanja di <a href="{{link}}">keranjang Mercatoria</a>.</p>',
                'coin_expiring' => '<p>Halo {{nama_user}},</p><p>{{jumlah_koin}} koinmu akan kedaluwarsa dalam {{masa}}. Gunakan sebelum hilang.</p><p><a href="{{link}}">Lihat akunmu</a>.</p>',
                'birthday_reward' => '<p>Halo {{nama_user}},</p><p>Selamat ulang tahun! Kamu menerima {{jumlah_koin}} koin dan voucher {{voucher}} yang berlaku 30 hari dengan minimum belanja Rp100.000.</p><p><a href="{{link}}">Lihat hadiahmu</a>.</p>',
                'coin_earned' => '<p>Halo {{nama_user}},</p><p>{{jumlah_koin}} koin telah ditambahkan ke wallet-mu.</p><p><a href="{{link}}">Lihat riwayat koin</a>.</p>',
                'referral_reward' => '<p>Halo {{nama_user}},</p><p>Kamu menerima {{jumlah_koin}} koin karena teman undanganmu menyelesaikan order pertamanya.</p><p><a href="{{link}}">Lihat wallet</a>.</p>',
                'referee_reward' => '<p>Halo {{nama_user}},</p><p>Kamu menerima {{jumlah_koin}} koin setelah pembayaran pertamamu diverifikasi.</p><p><a href="{{link}}">Lihat wallet</a>.</p>',
                default => '<p>Halo {{nama_user}},</p><p>Ini adalah template '.$name.'. Silakan edit di admin panel.</p>',
            };
            EmailTemplate::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'from_email' => $from,
                    'subject' => $subject,
                    'body' => $body,
                    'variables' => ['nama_user', 'email', 'no_order', 'link', 'tanggal', 'nama_toko', 'otp', 'jumlah_koin', 'masa', 'voucher'],
                    'is_active' => true,
                ]
            );
        }
    }
}
