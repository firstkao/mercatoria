@extends('layouts.admin')

@section('title', 'Daftar Spammer')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">Daftar Spammer</h1>
            <p class="mt-1 text-sm text-text-muted">Identitas yang diblokir, riwayat IP, dan masa grace period.</p>
        </div>
        <form method="GET" action="{{ route('office.spammers.index') }}" class="flex flex-wrap gap-2">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Email atau nomor HP"
                   class="rounded-lg border border-border px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">Semua status</option>
                <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Diblokir</option>
                <option value="watchlist" @selected(($filters['status'] ?? '') === 'watchlist')>Watchlist</option>
            </select>
            <button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-semibold text-white">Cari</button>
        </form>
    </div>

    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mt-6 rounded-xl border border-border bg-white p-5">
        <h2 class="text-lg font-semibold text-navy-500">Tambah Spammer</h2>
        <form method="POST" action="{{ route('office.spammers.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
            @csrf
            <label class="text-sm font-medium">Email
                <input name="email" type="email" value="{{ old('email') }}"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">No. HP (format 62xxx)
                <input name="no_hp" value="{{ old('no_hp') }}" placeholder="628123456789"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Alasan
                <select name="reason" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    <option value="manual" @selected(old('reason', 'manual') === 'manual')>Blokir manual</option>
                    <option value="no_purchase_3x" @selected(old('reason') === 'no_purchase_3x')>3 kali daftar tanpa belanja</option>
                </select>
            </label>
            <label class="text-sm font-medium">Jumlah attempt IP
                <select name="attempt_count" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    @foreach (range(1, 3) as $attempt)
                        <option value="{{ $attempt }}" @selected((int) old('attempt_count', 1) === $attempt)>{{ $attempt }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-medium md:col-span-2">Alamat IP tambahan (opsional, satu IP per baris)
                <textarea name="ip_addresses" rows="3" placeholder="203.0.113.10"
                          class="mt-1 w-full rounded-lg border border-border px-3 py-2"></textarea>
                <span class="mt-1 block text-xs text-text-light">IP perangkat admin saat ini tidak dapat dimasukkan.</span>
            </label>
            <label class="text-sm font-medium md:col-span-2">Catatan admin
                <textarea name="admin_note" rows="2" maxlength="2000"
                          class="mt-1 w-full rounded-lg border border-border px-3 py-2">{{ old('admin_note') }}</textarea>
            </label>
            <button class="w-fit rounded-lg bg-danger-500 px-4 py-2 text-sm font-semibold text-white">Blokir identitas</button>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-xl border border-border bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-surface-2 text-xs uppercase text-text-muted">
                    <tr>
                        <th class="px-4 py-3">Email / HP</th>
                        <th class="px-4 py-3">Alasan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">IP &amp; Attempt</th>
                        <th class="px-4 py-3">Grace period</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($spammers as $spammer)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $spammer->email ?: '—' }}</div>
                                <div class="text-text-muted">{{ $spammer->no_hp ?: '—' }}</div>
                                @if ($spammer->admin_note)
                                    <div class="mt-1 max-w-xs text-xs text-text-light">{{ $spammer->admin_note }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $spammer->reason_enum->label() }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs {{ $spammer->isBlocked() ? 'bg-danger-50 text-danger-700' : 'bg-warning-50 text-warning-700' }}">
                                    {{ $spammer->isBlocked() ? 'Diblokir' : 'Watchlist' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @forelse ($spammer->ips as $ip)
                                    <div>{{ $ip->ip_address }} <span class="text-text-light">({{ $ip->attempt_count }}x)</span></div>
                                @empty
                                    <span class="text-text-light">Belum ada IP tercatat</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3">
                                {{ $spammer->unblocked_until?->format('d M Y H:i') ?? '—' }}
                                <div class="text-xs text-text-light">Dibuka {{ $spammer->unblock_count }}/2 kali</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($spammer->isBlocked() && $spammer->unblock_count < 2)
                                    <form method="POST" action="{{ route('office.spammers.unblock', $spammer) }}"
                                          onsubmit="return confirm('Buka blokir selama 7 hari dan hapus riwayat IP?')">
                                        @csrf
                                        <button class="rounded-lg border border-border px-3 py-2 text-xs font-semibold text-navy-500">Buka blokir 7 hari</button>
                                    </form>
                                @elseif ($spammer->unblock_count >= 2)
                                    <span class="text-xs text-text-light">Blokir permanen</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-text-muted">Belum ada data spammer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-border p-4">{{ $spammers->links() }}</div>
    </section>
@endsection
