@extends('admin.layouts.admin')

@section('title', 'Laporan')

@section('content')
    <h1 class="text-xl font-semibold">Laporan Penjualan</h1>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total DP Diterima</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($totalDp) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total Pelunasan</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($totalSettlement) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total Penjualan (Selesai)</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($totalPenjualan) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Outstanding (DP belum lunas)</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($outstanding) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Hangus (uang masuk, barang tidak keluar)</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($hangus) }}</p>
        </div>
    </div>

    <p class="mt-4 text-xs text-gray-400">
        Kartu denda menyusul bersama fitur denda (bagian h).
    </p>
@endsection
