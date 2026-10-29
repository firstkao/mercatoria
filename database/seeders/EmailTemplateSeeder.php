<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * 27 template email standar (PRD 24.2) — body memakai variabel standar 24.4.
     */
    public const TEMPLATES = [
        ['code' => 'verify_email', 'subject' => 'Verifikasi Email Mercatoria', 'body' => 'Halo {{nama_user}}, verifikasi email {{email}} untuk mengaktifkan akun {{nama_toko}}. {{link}}'],
        ['code' => 'reset_password', 'subject' => 'Reset Password Mercatoria', 'body' => 'Halo {{nama_user}}, atur ulang kata sandi melalui {{link}}.'],
        ['code' => 'admin_otp', 'subject' => 'Kode OTP Admin Mercatoria', 'body' => 'Kode OTP admin {{nama_toko}}: {{link}}. Berlaku 5 menit.'],
        ['code' => 'spammer_blocked', 'subject' => 'Akun Diblokir Mercatoria', 'body' => 'Akun {{email}} diblokir dari {{nama_toko}}.'],
        ['code' => 'crush_deleted', 'subject' => 'Akun Crush Dihapus Mercatoria', 'body' => 'Halo {{nama_user}}, akun lama kamu dihapus otomatis. Daftar ulang jika ingin kembali.'],
        ['code' => 'welcome', 'subject' => 'Selamat Datang di Mercatoria', 'body' => 'Halo {{nama_user}}, selamat bergabung di {{nama_toko}}!'],
        ['code' => 'order_created', 'subject' => 'Pesanan {{no_order}} Dibuat', 'body' => 'Halo {{nama_user}}, pesanan {{no_order}} senilai {{total}} berhasil dibuat.'],
        ['code' => 'payment_verified', 'subject' => 'Pembayaran {{no_order}} Terverifikasi', 'body' => 'Halo {{nama_user}}, pembayaran {{no_order}} telah diverifikasi.'],
        ['code' => 'payment_rejected', 'subject' => 'Pembayaran {{no_order}} Ditolak', 'body' => 'Halo {{nama_user}}, bukti pembayaran {{no_order}} ditolak. Silakan unggah ulang.'],
        ['code' => 'order_status_changed', 'subject' => 'Status Pesanan {{no_order}} Berubah', 'body' => 'Halo {{nama_user}}, status pesanan {{no_order}} kini {{status}}.'],
        ['code' => 'order_completed', 'subject' => 'Pesanan {{no_order}} Selesai', 'body' => 'Halo {{nama_user}}, pesanan {{no_order}} sudah selesai. Terima kasih!'],
        ['code' => 'settlement_reminder', 'subject' => 'Reminder Pelunasan {{no_order}}', 'body' => 'Halo {{nama_user}}, segera lakukan CO pelunasan {{no_order}} sebelum batas waktu.'],
        ['code' => 'order_hangus', 'subject' => 'Pesanan {{no_order}} Hangus', 'body' => 'Halo {{nama_user}}, pesanan {{no_order}} melewati batas 7 hari dan hangus.'],
        ['code' => 'cart_reminder', 'subject' => 'Pengingat Keranjang Belanja Mercatoria', 'body' => 'Halo {{nama_user}}, ada item menunggu di keranjangmu. {{link}}'],
        ['code' => 'coin_earned', 'subject' => 'Koin Bertambah', 'body' => 'Halo {{nama_user}}, koin kamu bertambah. Cek saldo di {{link}}.'],
        ['code' => 'coin_expiring', 'subject' => 'Koin Akan Kedaluwarsa', 'body' => 'Halo {{nama_user}}, koin kamu akan kedaluwarsa. Gunakan sebelum hangus!'],
        ['code' => 'voucher_received', 'subject' => 'Voucher Diterima', 'body' => 'Halo {{nama_user}}, kamu menerima voucher baru dari {{nama_toko}}.'],
        ['code' => 'voucher_expiring', 'subject' => 'Voucher Akan Kedaluwarsa', 'body' => 'Halo {{nama_user}}, voucher kamu akan expired pada {{tanggal}}.'],
        ['code' => 'birthday_reward', 'subject' => 'Selamat Ulang Tahun dari Mercatoria', 'body' => 'Selamat ulang tahun, {{nama_user}}! Nikmati hadiah dari {{nama_toko}}.'],
        ['code' => 'referral_reward', 'subject' => 'Bonus Referral', 'body' => 'Halo {{nama_user}}, kamu mendapat bonus referral dari {{nama_toko}}.'],
        ['code' => 'referee_reward', 'subject' => 'Bonus Undangan', 'body' => 'Halo {{nama_user}}, terima kasih sudah bergabung via undangan!'],
        ['code' => 'state_warning_h7', 'subject' => 'Peringatan Status Akun', 'body' => 'Halo {{nama_user}}, akunmu akan berubah status dalam 7 hari. Segera order!'],
        ['code' => 'state_changed', 'subject' => 'Status Akun Berubah', 'body' => 'Halo {{nama_user}}, status akunmu kini {{state}}.'],
        ['code' => 'cs_reply', 'subject' => 'Balasan dari CS Mercatoria', 'body' => 'Halo {{nama_user}}, berikut balasan CS {{nama_toko}}.'],
        ['code' => 'cs_followup', 'subject' => 'Follow-up Pesanan {{no_order}}', 'body' => 'Halo {{nama_user}}, kami ingin menindaklanjuti pesanan {{no_order}}.'],
        ['code' => 'admin_payment_notification', 'subject' => 'Notif Admin — Pembayaran Masuk', 'body' => 'Bukti bayar {{no_order}} dari {{nama_user}} ({{email}}) senilai {{total}} masuk. {{link}}'],
        ['code' => 'user_gift_notification', 'subject' => 'Hadiah dari Mercatoria', 'body' => 'Halo {{nama_user}}, admin {{nama_toko}} mengirim hadiah untukmu. {{link}}'],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $template) {
            EmailTemplate::updateOrCreate(
                ['code' => $template['code']],
                ['subject' => $template['subject'], 'body' => $template['body'], 'is_active' => true]
            );
        }
    }
}
