<?php

/*
|--------------------------------------------------------------------------
| Mapping Status Order (PRD 18) — SATU sumber label + warna
|--------------------------------------------------------------------------
| Dipakai helper order_status_label()/order_status_color(), enum
| OrderStatus::label(), dan komponen <x-order-status-badge>.
| color = kelas Tailwind badge (Design Spec).
*/

return [
    'menunggu_pembayaran' => ['label' => 'Menunggu Pembayaran', 'color' => 'bg-amber-100 text-amber-800'],
    'ditahan' => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-sky-100 text-sky-800'],
    'pembayaran_diterima' => ['label' => 'Pembayaran Diterima', 'color' => 'bg-green-100 text-green-800'],
    'sedang_diproses' => ['label' => 'Sedang Diproses', 'color' => 'bg-sky-100 text-sky-800'],
    'sampai_wh_china' => ['label' => 'Sampai WH China', 'color' => 'bg-sky-100 text-sky-800'],
    'dikirim_ke_indonesia' => ['label' => 'Dikirim ke Indonesia', 'color' => 'bg-sky-100 text-sky-800'],
    'bea_cukai' => ['label' => 'Bea Cukai', 'color' => 'bg-sky-100 text-sky-800'],
    'sampai_wh_indonesia' => ['label' => 'Sampai WH Indonesia', 'color' => 'bg-sky-100 text-sky-800'],
    'menunggu_pelunasan_pengambilan' => ['label' => 'Menunggu Pelunasan & Pengambilan', 'color' => 'bg-amber-100 text-amber-800'],
    'sedang_dikemas' => ['label' => 'Sedang Dikemas', 'color' => 'bg-sky-100 text-sky-800'],
    'selesai' => ['label' => 'Selesai', 'color' => 'bg-green-100 text-green-800'],
    'pembayaran_gagal' => ['label' => 'Pembayaran Gagal', 'color' => 'bg-red-100 text-red-800'],
    'dana_dikembalikan' => ['label' => 'Dana Dikembalikan', 'color' => 'bg-orange-100 text-orange-800'],
    'hangus' => ['label' => 'Hangus', 'color' => 'bg-red-100 text-red-800'],
    'menunggu_denda' => ['label' => 'Menunggu Denda', 'color' => 'bg-amber-100 text-amber-800'],
    'hangus_permanen' => ['label' => 'Hangus Permanen', 'color' => 'bg-red-100 text-red-800'],
    'slot_sold' => ['label' => 'Slot Sold', 'color' => 'bg-red-100 text-red-800'],
];
