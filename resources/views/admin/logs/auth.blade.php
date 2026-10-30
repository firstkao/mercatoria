@extends('admin.layouts.admin')

@section('title', 'Log Sistem')

@section('content')
@php
    $routeName = request()->route()->getName();
    $currentTab = match(true) {
        str_contains($routeName, 'activity') => 'activity-logs',
        str_contains($routeName, 'auth')     => 'auth-logs',
        default                              => 'admin-logs',
    };
    $tabRoutes = [
        'activity-logs' => 'office.activity-logs',
        'auth-logs'     => 'office.auth-logs',
        'admin-logs'    => 'office.admin-logs',
    ];
    $currentRoute = $tabRoutes[$currentTab] ?? 'office.auth-logs';

    $actionLabels = [
        'login'         => ['label' => 'Berhasil Masuk',    'icon' => 'box-arrow-in-right',   'color' => 'green'],
        'login_failed'  => ['label' => 'Gagal Masuk',       'icon' => 'exclamation-triangle', 'color' => 'red'],
        'logout'        => ['label' => 'Keluar',            'icon' => 'box-arrow-right',      'color' => 'gray'],
        'register'      => ['label' => 'Daftar Akun',       'icon' => 'person-plus',          'color' => 'green'],
        'password_reset'=> ['label' => 'Reset Kata Sandi',  'icon' => 'key',                  'color' => 'amber'],
    ];

    $reasonLabels = [
        'kredensial salah'          => 'Email atau kata sandi salah',
        'rate limit'                => 'Terlalu banyak percobaan',
        'akun terkunci'             => 'Akun terkunci sementara',
        'email belum diverifikasi'  => 'Email belum diverifikasi',
        'token expired'             => 'Token sudah kedaluwarsa',
        'token invalid'             => 'Token tidak valid',
        'akun diblokir'             => 'Akun diblokir',
        'ip diblokir'               => 'IP diblokir',
        'bot terdeteksi'            => 'Bot/crawler terdeteksi',
        'honeypot triggered'        => 'Form spam terdeteksi',
        'turnstile failed'          => 'Verifikasi keamanan gagal',
    ];

    $colorMap = [
        'green' => 'bg-[#E8F5E9] text-[#2E7D32] border-[#C8E6C9]',
        'red'   => 'bg-[#FDECEA] text-[#9E2B25] border-[#F5C6C2]',
        'amber' => 'bg-[#FFF8E1] text-[#8D6E00] border-[#F5E6A3]',
        'blue'  => 'bg-[#E8F0FE] text-[#1A56A8] border-[#C5D9F7]',
        'gray'  => 'bg-[#F5F2EC] text-[#57534E] border-[#D8D2C4]',
    ];
@endphp

{{-- Header --}}
<div class="flex items-end justify-between">
    <div>
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Keamanan</p>
        <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Log Sistem</h1>
        <p class="mt-1 text-sm text-ink/55">Retensi otomatis 30 hari &mdash; Cron 03:00 WIB</p>
    </div>
    @if(Route::has('office.logs.export'))
        <a href="{{ route('office.logs.export', ['tab' => $currentTab]) }}"
           class="rounded-lg border border-rule bg-white px-4 py-2 text-sm font-medium text-ink transition-colors hover:bg-paper">
            <i class="bi bi-download me-1.5" style="font-size:0.85rem;"></i> Export CSV
        </a>
    @endif
</div>

{{-- Tab Menu --}}
<div class="mt-5 flex gap-1 rounded-lg border border-rule bg-white p-1">
    @if(Route::has('office.activity-logs'))
        <a href="{{ route('office.activity-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'activity-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-activity me-1.5" style="font-size:0.85rem;"></i> Activity Log
        </a>
    @endif
    @if(Route::has('office.auth-logs'))
        <a href="{{ route('office.auth-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'auth-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-shield-lock me-1.5" style="font-size:0.85rem;"></i> Auth Log
        </a>
    @endif
    @if(Route::has('office.admin-logs'))
        <a href="{{ route('office.admin-logs') }}"
           class="flex-1 rounded-md px-4 py-2 text-center text-sm font-medium transition-colors
                  {{ $currentTab === 'admin-logs' ? 'bg-ink text-white' : 'text-ink/65 hover:bg-paper' }}">
            <i class="bi bi-person-gear me-1.5" style="font-size:0.85rem;"></i> Admin Log
        </a>
    @endif
</div>

{{-- Filter Bar --}}
<div class="mt-4 flex flex-wrap items-center gap-2">
    <form method="GET" action="{{ route($currentRoute) }}" class="flex flex-1 flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari email / IP / alasan..."
               class="w-64 rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink placeholder-ink/35 focus:outline-none focus:ring-2 focus:ring-accent/45">

        <select name="action"
                class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            <option value="">Semua Aksi</option>
            @foreach ($actionLabels as $key => $a)
                <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $a['label'] }}</option>
            @endforeach
        </select>

        <select name="date"
                class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            <option value="">Semua Waktu</option>
            <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week"  {{ request('date') === 'week'  ? 'selected' : '' }}>7 Hari</option>
            <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>30 Hari</option>
        </select>

        <button type="submit"
                class="rounded-lg bg-ink px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-ink/85">
            <i class="bi bi-funnel me-1" style="font-size:0.85rem;"></i> Filter
        </button>

        @if(request()->hasAny(['search', 'action', 'date']))
            <a href="{{ route($currentRoute) }}"
               class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink/60 transition-colors hover:bg-paper hover:text-ink">
                <i class="bi bi-x-circle me-1"></i> Reset
            </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-rule bg-paper">
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Waktu (WIB)</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Email</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Aksi</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">Alasan</th>
                <th class="px-4 py-3 text-left font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-rule/50">
            @forelse ($logs as $log)
                @php
                    $action = $actionLabels[$log->action] ?? [
                        'label' => ucwords(str_replace(['_', '-'], ' ', $log->action)),
                        'icon'  => 'dot',
                        'color' => 'gray',
                    ];
                    $color = $colorMap[$action['color']];

                    // Terjemahkan alasan ke bahasa manusia
                    $reasonRaw = $log->reason ?? $log->description ?? null;
                    $reasonText = null;
                    if ($reasonRaw) {
                        $reasonText = $reasonLabels[$reasonRaw] ?? ucwords(str_replace(['_', '-'], ' ', $reasonRaw));
                    }

                    $isFailed = str_contains($log->action, 'failed');
                @endphp
                <tr class="transition-colors {{ $isFailed ? 'bg-red-50/50' : '' }} hover:bg-paper/60">
                    {{-- Waktu --}}
                    <td class="whitespace-nowrap px-4 py-3">
                        <p class="text-xs font-medium text-ink">
                            {{ $log->created_at->translatedFormat('d M Y') }}
                        </p>
                        <p class="font-mono text-[11px] text-ink/45">
                            {{ $log->created_at->format('H:i') }} WIB
                        </p>
                    </td>

                    {{-- Email --}}
                    <td class="px-4 py-3">
                        @if($log->email_attempt)
                            <code class="rounded bg-paper px-1.5 py-0.5 font-mono text-xs text-ink/70">
                                {{ $log->email_attempt }}
                            </code>
                        @elseif($log->user ?? null)
                            <p class="text-sm font-medium text-ink">{{ $log->user->name }}</p>
                            <p class="text-xs text-ink/45">{{ $log->user->email }}</p>
                        @else
                            <span class="font-mono text-xs text-ink/35">Tamu</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium {{ $color }}">
                            <i class="bi bi-{{ $action['icon'] }}" style="font-size:0.75rem;"></i>
                            {{ $action['label'] }}
                        </span>
                    </td>

                    {{-- Alasan --}}
                    <td class="px-4 py-3 text-sm text-ink/70">
                        {{ $reasonText ?? '—' }}
                    </td>

                    {{-- IP --}}
                    <td class="px-4 py-3">
                        <code class="rounded bg-paper px-1.5 py-0.5 font-mono text-[11px] text-ink/55">
                            {{ $log->ip_address ?? '—' }}
                        </code>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center">
                        <i class="bi bi-shield-lock text-ink/20" style="font-size:2.5rem;"></i>
                        <p class="mt-3 text-sm text-ink/45">Belum ada aktivitas autentikasi tercatat.</p>
                        @if(request()->hasAny(['search', 'action', 'date']))
                            <a href="{{ route($currentRoute) }}" class="mt-2 inline-block text-sm text-accent hover:underline">
                                Reset filter
                            </a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
    <div class="mt-5">
        {{ $logs->links() }}
    </div>
@endif
@endsection