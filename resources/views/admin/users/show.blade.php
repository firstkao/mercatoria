@extends('layouts.admin')

@section('title', 'Detail Customer')

@section('content')
@php
    $profileComplete = $user->tgl_lahir !== null;
    $emailVerified = $user->hasVerifiedEmail();
@endphp
<a href="{{ route('office.users.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke User List</a>

@error('password_reset')
    <div class="mt-4 rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700">{{ $message }}</div>
@enderror

<section class="mt-4 flex flex-wrap items-start gap-5 rounded-xl border border-border bg-white p-5">
    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-primary-100 text-3xl font-bold text-navy-500" aria-hidden="true">
        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
    <div class="min-w-0 flex-1">
        <h1 class="text-2xl font-bold text-navy-500">{{ $user->name }}</h1>
        <p class="mt-1 text-sm text-text-muted">{{ $user->email }} · {{ $user->no_hp ?: '—' }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <x-badge-state :user="$user" />
            @if ($user->isMigrated())
                <span class="inline-flex items-center rounded-full bg-[#E3F2FD] px-3 py-1 text-xs font-semibold text-[#1E88E5]">🔄 Migrated</span>
            @endif
        </div>
        <p class="mt-3 text-sm text-text-muted">
            Terdaftar: {{ $user->created_at?->translatedFormat('j M Y') }} · Last Login: {{ $user->last_login?->translatedFormat('j M Y H:i') ?? '—' }}
        </p>
        <p class="mt-2 text-sm">Status Email:
            @if ($emailVerified)
                <span class="font-semibold text-success-500">✅ VERIFIED</span>
            @else
                <span class="font-semibold text-warning-700">⚠️ BELUM VERIFIED</span>
            @endif
        </p>
        <p class="text-sm">Status Profil:
            @if ($profileComplete)
                <span class="font-semibold text-success-500">✅ LENGKAP</span>
            @else
                <span class="font-semibold text-warning-700">⚠️ BELUM LENGKAP (tgl lahir kosong)</span>
            @endif
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <x-button variant="secondary" href="#kirim-gift">Kirim Gift</x-button>
        </div>
    </div>
</section>

<section class="mt-5 rounded-xl border border-border bg-[#F8F9FA] p-5">
    <h2 class="text-xs font-bold uppercase tracking-wide text-text-muted">⚙️ Admin Actions</h2>
    <div class="mt-4 space-y-3">
        <div class="rounded-lg border border-border bg-white p-4">
            <h3 class="font-semibold">Force Reset Password</h3>
            <p class="mt-1 text-sm text-text-muted">Kirim link reset password manual ke email user.</p>
            <x-button variant="warning" class="mt-3" @click="$dispatch('open-modal', 'force-reset-password')">🔄 Kirim Link Reset</x-button>
        </div>
        @unless ($emailVerified)
            <div class="rounded-lg border border-border bg-white p-4">
                <h3 class="font-semibold">Force Verify Email</h3>
                <p class="mt-1 text-sm text-text-muted">Tandai email sudah verified tanpa user klik link.</p>
                <p class="mt-1 text-sm text-warning-700">⚠️ Pakai ini kalau user gak bisa akses email lama.</p>
                <x-button variant="success" class="mt-3" @click="$dispatch('open-modal', 'force-verify-email')">✅ Force Verify</x-button>
            </div>
        @endunless
        @if ($blockedSpammer)
            <div class="rounded-lg border border-border bg-white p-4">
                <h3 class="font-semibold">Forever Delete</h3>
                <p class="mt-1 text-sm text-text-muted">Akun cocok dengan daftar spammer yang diblokir.</p>
                <x-button variant="danger" class="mt-3" @click="$dispatch('open-modal', 'forever-delete-spammer')">Forever Delete</x-button>
            </div>
        @endif
    </div>
</section>

<nav class="mt-5 flex flex-wrap gap-2" aria-label="Tab detail user">
    @foreach (['overview' => 'Overview', 'orders' => 'Orders', 'koin' => 'Koin', 'log' => 'Log'] as $key => $label)
        <a href="{{ route('office.users.show', ['user' => $user, 'tab' => $key]) }}"
           @if ($tab === $key) aria-current="page" @endif
           @class(['rounded-lg px-3 py-2 text-sm', 'bg-primary-300 font-semibold text-white' => $tab === $key, 'border border-border bg-white' => $tab !== $key])>{{ $label }}</a>
    @endforeach
</nav>

@if ($tab === 'overview')
<section class="mt-4 rounded-xl border border-border bg-white p-5">
    <h2 class="font-semibold">Informasi akun</h2>
    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
        <div><dt class="text-text-muted">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
        <div><dt class="text-text-muted">No. HP</dt><dd class="font-medium">{{ $user->no_hp ?: '—' }}</dd></div>
        <div><dt class="text-text-muted">Status verifikasi</dt><dd class="font-medium">{{ $emailVerified ? 'Terverifikasi' : 'Belum verifikasi' }}</dd></div>
        <div><dt class="text-text-muted">Tanggal lahir</dt><dd class="font-medium">{{ $user->tgl_lahir?->format('d M Y') ?? 'Belum diisi' }}</dd></div>
        <div class="sm:col-span-2">
            <dt class="text-text-muted">Alamat</dt>
            <dd class="font-medium">{{ collect([$user->alamat_lengkap, $user->kecamatan, $user->kota, $user->provinsi, $user->kode_pos])->filter()->join(', ') ?: 'Belum diisi' }}</dd>
        </div>
    </dl>
</section>

<div id="kirim-gift">
@if ($user->role === 'customer' && (! $user->isCrush() || $user->customer()->exists()))
    <section class="mt-5 rounded-xl border border-border bg-white p-5">
        <h2 class="font-semibold">Kirim gift</h2>
        <form method="POST" action="{{ route('office.users.gifts.store', $user) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <label class="text-sm font-medium">Jenis gift
                <select name="type" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    <option value="coin">Koin</option>
                    <option value="voucher">Voucher</option>
                    <option value="gift_box">Gift Box Special</option>
                </select>
            </label>
            <label class="text-sm font-medium">Nominal koin (untuk gift koin)
                <input type="number" name="coin_amount" min="1" max="1000000"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Voucher (untuk gift voucher)
                <select name="voucher_id" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    <option value="">Pilih voucher</option>
                    @foreach ($vouchers as $voucher)
                        <option value="{{ $voucher->id }}">{{ $voucher->code }} — {{ $voucher->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-medium">Catatan
                <input name="note" maxlength="1000" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <div class="sm:col-span-2">
                <x-button type="submit">Kirim Gift</x-button>
            </div>
        </form>
    </section>
@endif
</div>
@elseif ($tab === 'orders')
<div class="mt-4 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted"><tr><th class="px-4 py-3">No. Order</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Tanggal</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse ($orders as $order)
                <tr>
                    <td class="px-4 py-3"><a href="{{ route('office.orders.show', $order) }}" class="font-semibold text-navy-500 hover:underline">{{ $order->order_no }}</a></td>
                    <td class="px-4 py-3">{{ $order->status instanceof \BackedEnum ? $order->status->label() : $order->status }}</td>
                    <td class="px-4 py-3">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ $order->created_at?->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-text-muted">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@elseif ($tab === 'koin')
<section class="mt-4 rounded-xl border border-border bg-white p-5">
    <p class="text-sm text-text-muted">Saldo koin</p>
    <p class="text-2xl font-bold">{{ number_format($coinBalance, 0, ',', '.') }}</p>
    <ul class="mt-4 divide-y divide-border text-sm">
        @forelse ($coinTransactions as $transaction)
            <li class="flex justify-between gap-3 py-2">
                <span>{{ $transaction->description ?: $transaction->source }} <span class="text-text-muted">· {{ $transaction->created_at?->format('d M Y') }}</span></span>
                <span class="font-semibold">{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format((int) $transaction->amount, 0, ',', '.') }}</span>
            </li>
        @empty
            <li class="py-4 text-text-muted">Belum ada transaksi koin.</li>
        @endforelse
    </ul>
</section>
@else
<div class="mt-4 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted"><tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Detail</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse ($logs as $log)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at?->format('d M H:i') }}</td>
                    <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                    <td class="px-4 py-3 text-text-muted">{{ $log->description ?? $log->url ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-text-muted">Belum ada log aktivitas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

@if ($blockedSpammer)
    <x-modal name="forever-delete-spammer">
        <h2 class="text-lg font-bold">Hapus akun spammer permanen?</h2>
        <p class="mt-2 text-sm text-text-muted">Akun dan data turunannya akan dihapus. Identitas di daftar spammer tetap aktif untuk mencegah pendaftaran ulang.</p>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="$dispatch('close-modal', 'forever-delete-spammer')">Batal</x-button>
            <form method="POST" action="{{ route('office.users.forever-delete', $user) }}">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Hapus Permanen</x-button>
            </form>
        </div>
    </x-modal>
@endif

<x-modal name="force-reset-password" max-width="max-w-[520px]">
    <h2 class="text-lg font-bold">🔄 Force Reset Password</h2>
    <p class="mt-3 text-sm">Kirim link reset password ke:</p>
    <p class="mt-1 font-semibold">📧 {{ $user->email }}</p>
    <p class="mt-2 text-sm text-text-muted">ℹ️ Link berlaku 24 jam. User bisa set password baru via link ini.</p>
    <form method="POST" action="{{ route('office.users.force-reset-password', $user) }}" class="mt-4">
        @csrf
        <label for="reset-note" class="block text-sm font-medium">Catatan admin (opsional):</label>
        <input id="reset-note" name="note" maxlength="500" class="mt-1 w-full rounded-lg border border-border px-3 py-2 text-sm">
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="$dispatch('close-modal', 'force-reset-password')">Batal</x-button>
            <x-button type="submit" variant="warning">Kirim Link Reset</x-button>
        </div>
    </form>
</x-modal>

@unless ($emailVerified)
    <x-modal name="force-verify-email" max-width="max-w-[520px]">
        <h2 class="text-lg font-bold">✅ Force Verify Email</h2>
        <p class="mt-3 text-sm">Tandai email ini sebagai verified?</p>
        <p class="mt-1 font-semibold">📧 {{ $user->email }}</p>
        <div class="mt-3 rounded-lg border-l-4 border-[#FB8C00] bg-[#FFF8E1] p-3 text-sm">
            <p class="font-semibold">⚠️ Peringatan:</p>
            <p class="mt-1">Pastikan kamu YAKIN email ini valid &amp; benar. Ini cuma dipakai kalau user gak bisa akses email.</p>
            <p class="mt-1">Action ini tercatat di admin log.</p>
        </div>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="$dispatch('close-modal', 'force-verify-email')">Batal</x-button>
            <form method="POST" action="{{ route('office.users.force-verify-email', $user) }}">
                @csrf
                <x-button type="submit" variant="success">Force Verify</x-button>
            </form>
        </div>
    </x-modal>
@endunless
@endsection
