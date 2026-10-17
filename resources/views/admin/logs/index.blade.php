@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <p class="text-sm text-text-muted">Log disimpan selama 30 hari sesuai kebijakan retensi.</p>
    </div>
    <a href="{{ route('office.logs.export', ['kind' => $kind] + request()->query()) }}"
        class="rounded-lg border border-border bg-white px-4 py-2 text-sm font-medium hover:bg-surface-2">
        Export CSV
    </a>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    @foreach (['activity' => 'Activity', 'auth' => 'Auth', 'admin' => 'Admin'] as $tab => $label)
        <a href="{{ route('office.logs.'.$tab) }}"
            @class(['rounded-lg px-3 py-2 text-sm', 'bg-navy-500 text-white' => $kind === $tab, 'border border-border bg-white' => $kind !== $tab])>
            {{ $label }}
        </a>
    @endforeach
</div>

<form method="GET" class="mb-4 grid gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
    <input name="q" value="{{ $filters['q'] }}" placeholder="Cari isi, pengguna, IP..."
        class="rounded-lg border border-border px-3 py-2 text-sm">
    <input name="action" value="{{ $filters['action'] }}" placeholder="Action"
        class="rounded-lg border border-border px-3 py-2 text-sm">
    <label class="text-xs text-text-muted">Dari
        <input name="from" type="date" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2 text-sm">
    </label>
    <label class="text-xs text-text-muted">Sampai
        <input name="to" type="date" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2 text-sm">
    </label>
    <button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Terapkan</button>
</form>

<div class="overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted">
            <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Pengguna</th>
                <th class="px-4 py-3">Action</th>
                <th class="px-4 py-3">Detail</th>
                <th class="px-4 py-3">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($logs as $log)
                <tr @class(['bg-red-50' => $kind === 'auth' && str_contains($log->action, 'failed')])>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                    <td class="px-4 py-3">
                        {{ $kind === 'admin' ? ($log->admin?->email ?? '—') : ($log->user?->email ?? $log->email_attempt ?? '—') }}
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                    <td class="max-w-xl px-4 py-3">
                        @if ($kind === 'admin')
                            <details>
                                <summary class="cursor-pointer">{{ $log->description ?: $log->model ?: 'Perubahan nilai' }}</summary>
                                <pre class="mt-2 whitespace-pre-wrap text-xs">Lama: {{ json_encode($log->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
Baru: {{ json_encode($log->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @else
                            {{ $log->description ?? $log->reason ?? $log->url ?? '—' }}
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->ip_address ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-text-muted">Tidak ada data log yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
