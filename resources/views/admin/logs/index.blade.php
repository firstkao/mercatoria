@extends('layouts.admin')

@section('title', $title)

@section('content')
@php
    $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $tabs = ['activity' => 'Activity Log', 'auth' => 'Auth Log', 'admin' => 'Admin Log'];
@endphp
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Log Sistem</h1>
        <p class="text-sm text-text-muted">Retensi otomatis 30 hari — cron 03:00 WIB.</p>
    </div>
    <a href="{{ route('office.logs.export', ['kind' => $kind] + request()->query()) }}"
        class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-medium hover:bg-surface-2">
        Export CSV
    </a>
</div>

<div class="mb-4 flex flex-wrap items-center gap-2">
    <span class="text-sm text-text-muted">Menu:</span>
    @foreach ($tabs as $tab => $label)
        <a href="{{ route('office.logs.'.$tab) }}"
            @if ($kind === $tab) aria-current="page" @endif
            @class(['rounded-lg px-3 py-2 text-sm', 'bg-primary-300 font-semibold text-white' => $kind === $tab, 'border border-border bg-white' => $kind !== $tab])>
            {{ $kind === $tab ? '● ' : '' }}{{ $label }}
        </a>
    @endforeach
</div>

<form method="GET" class="mb-4 grid gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr_auto_auto] lg:items-end">
    <input name="q" value="{{ $filters['q'] }}" placeholder="Cari user/IP/action..." aria-label="Cari log"
        class="rounded-lg border border-border px-3 py-2 text-sm">
    <select name="action" aria-label="Action" class="rounded-lg border border-border px-3 py-2 text-sm">
        <option value="">Action ▾</option>
        @foreach ($actions as $action)
            <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
        @endforeach
    </select>
    <label class="text-xs text-text-muted">Dari
        <input name="from" type="date" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2 text-sm">
    </label>
    <label class="text-xs text-text-muted">Sampai
        <input name="to" type="date" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2 text-sm">
    </label>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="group" value="1" @checked($filters['group']) class="rounded border-border"> Group
    </label>
    <div class="flex gap-2">
        <button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Terapkan</button>
        <a href="{{ route('office.logs.'.$kind) }}" class="rounded-lg border border-border px-3 py-2 text-sm" aria-label="Reset filter">✕</a>
    </div>
</form>

<div class="overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted">
            <tr>
                <th class="px-4 py-3">Waktu (WIB)</th>
                <th class="px-4 py-3">User/Admin</th>
                <th class="px-4 py-3">Action</th>
                <th class="px-4 py-3">IP Address</th>
                <th class="px-4 py-3">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($logs as $log)
                @php
                    $failed = $kind === 'auth' && str_contains($log->action, 'failed');
                    $person = $kind === 'admin'
                        ? ($log->admin?->name ?? $log->admin?->email ?? '—')
                        : ($log->user?->name ?? $log->email_attempt ?? 'Guest');
                    $payload = match ($kind) {
                        'activity' => ['description' => $log->description, 'url' => $log->url, 'user_agent' => $log->user_agent, 'metadata' => $log->metadata],
                        'auth' => ['email_attempt' => $log->email_attempt, 'reason' => $log->reason, 'user_agent' => $log->user_agent],
                        default => ['model' => $log->model, 'model_id' => $log->model_id, 'description' => $log->description],
                    };
                @endphp
                <tr @class(['bg-[#FFEBEE]' => $failed])>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M H:i') }}</td>
                    <td class="px-4 py-3">{{ $person }}</td>
                    <td class="px-4 py-3 font-medium">{{ $log->action }}@if ($failed) (⚠️)@endif</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->ip_address ?: '—' }}</td>
                    <td class="max-w-xl px-4 py-3">
                        <details>
                            <summary class="cursor-pointer font-medium text-navy-500">{{ $kind === 'admin' ? '[Diff]' : '[JSON]' }}</summary>
                            @if ($kind === 'admin')
                                <p class="mt-2 text-xs text-text-muted">{{ $log->description ?: $log->model }}</p>
                                <div class="mt-2 grid gap-2 md:grid-cols-2">
                                    <div><p class="text-xs font-semibold">old_value</p><pre class="whitespace-pre-wrap break-all rounded bg-surface-2 p-2 text-xs">{{ json_encode($log->old_value, $flags) }}</pre></div>
                                    <div><p class="text-xs font-semibold">new_value</p><pre class="whitespace-pre-wrap break-all rounded bg-surface-2 p-2 text-xs">{{ json_encode($log->new_value, $flags) }}</pre></div>
                                </div>
                            @else
                                <pre class="mt-2 whitespace-pre-wrap break-all rounded bg-surface-2 p-2 text-xs">{{ json_encode($payload, $flags) }}</pre>
                            @endif
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-text-muted">Tidak ada data log yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($kind === 'auth')
    <p class="mt-2 text-xs text-text-muted">Baris login gagal ditandai merah muda.</p>
@elseif ($kind === 'admin')
    <p class="mt-2 text-xs text-text-muted">Detail menampilkan perbandingan old_value vs new_value (JSON).</p>
@endif
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
