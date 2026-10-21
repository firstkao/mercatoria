@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
@php
    $values = array_column($chart, 'value');
    $max = max($values) ?: 1;
    $step = 10 ** max(0, strlen((string) (int) $max) - 1);
    $ceil = (int) (ceil($max / $step) * $step);
    $w = 600; $h = 160;
    $points = collect($chart)->map(fn ($p, $i) => round($i * $w / 29, 1).','.round($h - ($p['value'] / $ceil) * $h, 1))->implode(' ');
    $format = fn (float $v) => $v >= 1_000_000 ? 'Rp '.rtrim(rtrim(number_format($v / 1_000_000, 1, ',', '.'), '0'), ',').'jt' : 'Rp '.number_format($v / 1000, 0, ',', '.').'rb';
@endphp

<div class="flex flex-wrap items-center justify-between gap-2">
    <h1 class="text-2xl font-bold text-navy-500">Dashboard Admin</h1>
    <p class="text-sm text-text-muted">{{ $today->translatedFormat('d M Y') }} (WIB)</p>
</div>

<div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
    @foreach ($stats as $stat)
        <section class="rounded-xl border border-border bg-white p-4">
            <h2 class="text-[11px] font-semibold uppercase tracking-wide text-text-muted lg:text-xs">{{ $stat['label'] }}</h2>
            <p class="mt-2 text-xl font-bold lg:text-2xl">{{ $stat['value'] }}</p>
            <p @class(['mt-1 text-xs', 'text-success-500' => $stat['trend'] >= 0, 'text-danger-500' => $stat['trend'] < 0])>
                {{ $stat['trend'] < 0 ? '▼' : '▲' }} {{ $stat['change'] }}
            </p>
        </section>
    @endforeach
</div>

<section class="mt-5 rounded-xl border border-border bg-white p-4 md:p-5">
    <h2 class="font-semibold">📈 Grafik Penjualan 30 Hari Terakhir</h2>
    <div class="mt-4 flex gap-2">
        <div class="flex h-40 flex-col justify-between text-right text-[11px] text-text-muted" aria-hidden="true">
            <span>{{ $format($ceil) }}</span><span>{{ $format($ceil / 2) }}</span><span>Rp 0</span>
        </div>
        <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" class="h-40 w-full" role="img"
             aria-label="Grafik penjualan 30 hari terakhir">
            <line x1="0" y1="0" x2="{{ $w }}" y2="0" stroke="#E0E0E0" stroke-width="1" />
            <line x1="0" y1="{{ $h / 2 }}" x2="{{ $w }}" y2="{{ $h / 2 }}" stroke="#E0E0E0" stroke-width="1" />
            <line x1="0" y1="{{ $h }}" x2="{{ $w }}" y2="{{ $h }}" stroke="#E0E0E0" stroke-width="1" />
            <polyline fill="none" stroke="#4FC3F7" stroke-width="2" vector-effect="non-scaling-stroke" points="{{ $points }}" />
        </svg>
    </div>
    <div class="mt-1 flex justify-between pl-14 text-[11px] text-text-muted">
        @foreach ([0, 7, 14, 21, 29] as $i)
            <span>{{ $chart[$i]['date']->translatedFormat('d M') }}</span>
        @endforeach
    </div>
</section>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <section class="rounded-xl border border-border bg-white p-4 md:p-5">
        <h2 class="font-semibold">🚨 Pesanan Menunggu Aksi</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($pending as $label => [$count, $unit, $url])
                <li class="flex justify-between gap-2">
                    <a href="{{ $url }}" class="hover:underline">• {{ $label }}</a>
                    <span @class(['font-semibold', 'text-danger-500' => $count > 0])>{{ $count }} {{ $unit }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-4 text-right">
            <a href="{{ route('office.orders.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Lihat Semua →</a>
        </div>
    </section>

    <section class="rounded-xl border border-border bg-white p-4 md:p-5">
        <h2 class="font-semibold">🏆 Top 5 Produk Terjual Bln Ini</h2>
        <ol class="mt-3 space-y-2 text-sm">
            @forelse ($topProducts as $product)
                <li class="flex justify-between gap-2">
                    <span>{{ $loop->iteration }}. @if ($product['game'])[{{ $product['game'] }}] @endif{{ $product['name'] }}</span>
                    <span class="shrink-0 text-text-muted">({{ $product['sold'] }}x)</span>
                </li>
            @empty
                <li class="text-text-muted">Belum ada penjualan bulan ini.</li>
            @endforelse
        </ol>
    </section>
</div>

<section class="mt-5 rounded-xl border border-border bg-white p-4 md:p-5">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold">🔔 5 Notifikasi Admin Terbaru</h2>
        <a href="{{ route('office.notifications.index') }}" class="text-sm font-semibold text-navy-500 hover:underline">Lihat semua</a>
    </div>
    <ul class="mt-3 space-y-2 text-sm">
        @forelse ($notifications as $notification)
            <li>
                <form method="POST" action="{{ route('office.notifications.open', $notification) }}">@csrf
                    <button type="submit" class="text-left hover:underline">
                        • <span class="font-semibold">[{{ $notification->title }}]</span> {{ $notification->message }}
                        <span class="text-text-muted">({{ $notification->created_at->diffForHumans() }})</span>
                    </button>
                </form>
            </li>
        @empty
            <li class="text-text-muted">Belum ada notifikasi.</li>
        @endforelse
    </ul>
</section>
@endsection
