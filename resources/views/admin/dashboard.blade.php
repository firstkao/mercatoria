@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-xl font-semibold">Dashboard Admin</h1>

    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Total Pesanan</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Pesanan Hari Ini</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($todayOrders) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">Pendapatan Bulan Ini</p>
            <p class="mt-1 text-2xl font-semibold">{{ format_rp($monthRevenue) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
            <p class="text-xs text-gray-500">User Baru Minggu Ini</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format($newUsers) }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Pesanan Menunggu Aksi</h2>
        <div class="mt-2 flex gap-6 text-sm">
            <a href="{{ route('office.payments.index') }}" class="text-sky-600 hover:underline">
                Pembayaran perlu verifikasi: <strong>{{ $pendingVerifications }}</strong>
            </a>
            <a href="{{ route('office.orders.index', ['status' => 'menunggu_pembayaran']) }}" class="text-sky-600 hover:underline">
                Order baru: <strong>{{ $newOrders }}</strong>
            </a>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-sm font-semibold">Produk Terjual (Top 5)</h2>
        <ul class="mt-2 space-y-1 text-sm">
            @forelse ($topProducts as $product)
                <li class="flex justify-between border-b border-gray-50 py-1">
                    <span>{{ $product->name }}</span>
                    <span>{{ $product->sold_count }} terjual</span>
                </li>
            @empty
                <li class="text-gray-400">Belum ada data.</li>
            @endforelse
        </ul>
    </div>
@endsection
