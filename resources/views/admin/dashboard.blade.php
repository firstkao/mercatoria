@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Back-Office</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Dashboard Admin</h1>
        </div>
        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink/50">{{ now()->format('d M Y') }} (WIB)</p>
    </div>

    {{-- 4 Stats Card (Design Spec 14.2) --}}
    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Total Pesanan</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pesanan Hari Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($todayOrders) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Pendapatan Bulan Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ format_rp($monthRevenue) }}</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">User Baru Minggu Ini</p>
            <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($newUsers) }}</p>
        </div>
    </div>

    {{-- Grafik 30 hari — slot kosong, data BE menyusul --}}
    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="flex items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">
            Grafik Penjualan 30 Hari
        </h2>
        <div class="mt-4 flex h-40 items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Grafik menyusul</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Pesanan Menunggu Aksi --}}
        <div class="rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Pesanan Menunggu Aksi</h2>
            <div class="mt-3 space-y-2 text-sm">
                <a href="{{ route('office.payments.index') }}" class="flex items-center justify-between rounded-lg border border-rule px-3 py-2 transition-colors hover:border-ink">
                    <span class="text-ink/80">Pembayaran perlu verifikasi</span>
                    <strong class="text-ink">{{ $pendingVerifications }}</strong>
                </a>
                <a href="{{ route('office.orders.index', ['status' => 'menunggu_pembayaran']) }}" class="flex items-center justify-between rounded-lg border border-rule px-3 py-2 transition-colors hover:border-ink">
                    <span class="text-ink/80">Order baru</span>
                    <strong class="text-ink">{{ $newOrders }}</strong>
                </a>
            </div>
        </div>

        {{-- Top 5 Produk --}}
        <div class="rounded-xl border border-rule bg-white p-4">
            <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Produk Terjual (Top 5)</h2>
            <ul class="mt-3 space-y-1 text-sm">
                @forelse ($topProducts as $product)
                    <li class="flex justify-between border-b border-rule/50 py-1.5">
                        <span class="text-ink/85">{{ $product->name }}</span>
                        <span class="font-mono text-xs text-ink/60">{{ $product->sold_count }} terjual</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-ink/45">Belum ada data.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Notifikasi terbaru — slot kosong, data BE menyusul --}}
    <div class="mt-6 rounded-xl border border-rule bg-white p-6">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Notifikasi Admin Terbaru</h2>
        <div class="mt-4 flex h-24 items-center justify-center rounded-lg border border-dashed border-rule bg-paper/40">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/40">Notifikasi menyusul</p>
        </div>
    </div>
@endsection
