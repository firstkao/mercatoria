@props(['action' => null, 'reason' => null])

@php
    $actionLabels = [
        'login' => 'Berhasil Masuk',
        'login_success' => 'Berhasil Masuk',
        'login_failed' => 'Gagal Masuk',
        'logout' => 'Keluar',
        'register' => 'Daftar Akun',
        'edit_profile' => 'Ubah Profil',
        'change_password' => 'Ganti Kata Sandi',
        'upload_payment_proof' => 'Unggah Bukti Bayar',
        'add_to_cart' => 'Tambah ke Keranjang',
        'remove_from_cart' => 'Hapus dari Keranjang',
        'order_created' => 'Buat Pesanan',
        'view_home' => 'Lihat Beranda',
        'view_catalog' => 'Lihat Katalog',
        'view_product' => 'Lihat Produk',
        'search' => 'Pencarian',
        'freeze_price' => 'Bekukan Harga',
        'begin_checkout' => 'Mulai Checkout',
        'use_coin' => 'Pakai Koin',
        'use_voucher' => 'Pakai Voucher',
        'share_product' => 'Bagikan Produk',
        'click_referral' => 'Klik Link Referral',
        'state_changed' => 'Status Berubah',
        'unblocked' => 'Dibuka Kuncinya',
        'download_invoice' => 'Unduh Invoice',
        'snapshot_created' => 'Snapshot Dibuat',
        'receive_note' => 'Terima Catatan',
        'spammer_blocked' => 'Spammer Diblokir',
        'bot_crawler' => 'Bot Terdeteksi',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'forever_delete' => 'Hapus Permanen',
    ];

    $reasonLabels = [
        'kredensial salah' => 'Email atau kata sandi salah',
        'identitas diblokir' => 'Identitas diblokir',
        'akun terkunci' => 'Terlalu banyak percobaan, akun terkunci',
        'rate limit' => 'Terlalu banyak percobaan',
        'spam detected' => 'Terdeteksi spam',
    ];
@endphp

@if ($action !== null)
    {{ $actionLabels[$action] ?? $action }}
@elseif ($reason !== null)
    {{ $reasonLabels[$reason] ?? $reason }}
@endif
