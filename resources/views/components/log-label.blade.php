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

    // Badge warna: hijau=success, merah=danger, amber=warning, biru=info, abu=neutral
    $badgeColors = [
        'login' => 'bg-[#E8F5E9] text-[#2E7D32]',
        'login_success' => 'bg-[#E8F5E9] text-[#2E7D32]',
        'login_failed' => 'bg-[#FFEBEE] text-[#C62828]',
        'logout' => 'bg-[#ECEFF1] text-[#546E7A]',
        'register' => 'bg-[#E3F2FD] text-[#1565C0]',
        'create' => 'bg-[#E8F5E9] text-[#2E7D32]',
        'update' => 'bg-[#E3F2FD] text-[#1565C0]',
        'delete' => 'bg-[#FFEBEE] text-[#C62828]',
        'forever_delete' => 'bg-[#FFEBEE] text-[#C62828]',
        'spammer_blocked' => 'bg-[#FFEBEE] text-[#C62828]',
        'bot_crawler' => 'bg-[#FFF8E1] text-[#F57F17]',
        'state_changed' => 'bg-[#FFF8E1] text-[#F57F17]',
        'unblocked' => 'bg-[#E8F5E9] text-[#2E7D32]',
        'edit_profile' => 'bg-[#E3F2FD] text-[#1565C0]',
        'change_password' => 'bg-[#FFF8E1] text-[#F57F17]',
        'upload_payment_proof' => 'bg-[#E3F2FD] text-[#1565C0]',
        'order_created' => 'bg-[#E8F5E9] text-[#2E7D32]',
        'use_coin' => 'bg-[#FFF8E1] text-[#F57F17]',
        'use_voucher' => 'bg-[#FFF8E1] text-[#F57F17]',
    ];
    $defaultBadge = 'bg-[#ECEFF1] text-[#546E7A]';
@endphp

@if ($action !== null)
    @php $label = $actionLabels[$action] ?? $action; $cls = $badgeColors[$action] ?? $defaultBadge; @endphp
    <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $cls }}">{{ $label }}</span>
@elseif ($reason !== null)
    {{ $reasonLabels[$reason] ?? $reason }}
@endif
