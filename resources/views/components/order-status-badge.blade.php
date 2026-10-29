@props(['status', 'size' => 'sm'])

@php
    $map = [
        'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-100 text-amber-800'],
        'ditahan' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-sky-100 text-sky-800'],
        'pembayaran_diterima' => ['label' => 'Pembayaran Diterima', 'class' => 'bg-green-100 text-green-800'],
        'sedang_diproses' => ['label' => 'Sedang Diproses', 'class' => 'bg-sky-100 text-sky-800'],
        'sampai_wh_china' => ['label' => 'Sampai WH China', 'class' => 'bg-sky-100 text-sky-800'],
        'dikirim_ke_indonesia' => ['label' => 'Dikirim ke Indonesia', 'class' => 'bg-sky-100 text-sky-800'],
        'bea_cukai' => ['label' => 'Bea Cukai', 'class' => 'bg-sky-100 text-sky-800'],
        'sampai_wh_indonesia' => ['label' => 'Sampai WH Indonesia', 'class' => 'bg-sky-100 text-sky-800'],
        'menunggu_pelunasan_pengambilan' => ['label' => 'Menunggu Pelunasan & Pengambilan', 'class' => 'bg-amber-100 text-amber-800'],
        'sedang_dikemas' => ['label' => 'Sedang Dikemas', 'class' => 'bg-sky-100 text-sky-800'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-800'],
        'pembayaran_gagal' => ['label' => 'Pembayaran Gagal', 'class' => 'bg-red-100 text-red-800'],
        'dana_dikembalikan' => ['label' => 'Dana Dikembalikan', 'class' => 'bg-orange-100 text-orange-800'],
        'hangus' => ['label' => 'Hangus', 'class' => 'bg-red-100 text-red-800'],
        'hangus_permanen' => ['label' => 'Hangus Permanen', 'class' => 'bg-red-100 text-red-800'],
        'menunggu_denda' => ['label' => 'Menunggu Denda', 'class' => 'bg-amber-100 text-amber-800'],
        'slot_sold' => ['label' => 'Slot Sold', 'class' => 'bg-red-100 text-red-800'],
    ];
    $meta = $map[$status] ?? ['label' => ucwords(str_replace('_', ' ', $status)), 'class' => 'bg-paper text-ink/70'];
    $sizeClass = $size === 'lg' ? 'px-3 py-1 text-sm' : 'px-2 py-0.5 text-[11px]';
@endphp

<span class="inline-flex items-center rounded {{ $sizeClass }} font-semibold {{ $meta['class'] }}">{{ $meta['label'] }}</span>
