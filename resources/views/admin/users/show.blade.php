@extends('layouts.admin')

@section('title', 'Detail Customer')

@section('content')
<div>
    <a href="{{ route('office.users.index') }}" class="text-sm text-navy-500 hover:underline">← Kembali ke Customer</a>
    <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $user->name }}</h1>
    <p class="mt-1 text-sm text-text-muted">{{ $user->isMigrated() ? 'Customer hasil migrasi' : 'Customer terdaftar' }}</p>
</div>

@error('password_reset')
    <div class="mt-4 rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700">{{ $message }}</div>
@enderror

<section class="mt-5 rounded-xl border border-border bg-white p-5">
    <h2 class="font-semibold">Informasi akun</h2>
    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
        <div><dt class="text-text-muted">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
        <div><dt class="text-text-muted">No. HP</dt><dd class="font-medium">{{ $user->no_hp ?: '—' }}</dd></div>
        <div><dt class="text-text-muted">Status verifikasi</dt><dd class="font-medium">{{ $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum verifikasi' }}</dd></div>
        <div><dt class="text-text-muted">Tanggal lahir</dt><dd class="font-medium">{{ $user->tgl_lahir?->format('d M Y') ?? 'Belum diisi' }}</dd></div>
        <div class="sm:col-span-2">
            <dt class="text-text-muted">Alamat</dt>
            <dd class="font-medium">{{ collect([$user->alamat_lengkap, $user->kecamatan, $user->kota, $user->provinsi, $user->kode_pos])->filter()->join(', ') ?: 'Belum diisi' }}</dd>
        </div>
    </dl>
</section>

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

<section class="mt-5 rounded-xl border border-border bg-white p-5">
    <h2 class="font-semibold">Bantuan aktivasi</h2>
    <p class="mt-1 text-sm text-text-muted">Aksi admin dicatat pada log. Konfirmasi akan diminta sebelum dijalankan.</p>
    <div class="mt-4 flex flex-wrap gap-3">
        <x-button variant="ghost" @click="$dispatch('open-modal', 'force-reset-password')">Force Reset Password</x-button>
        @unless ($user->hasVerifiedEmail())
            <x-button variant="ghost" @click="$dispatch('open-modal', 'force-verify-email')">Force Verify Email</x-button>
        @endunless
        @if ($blockedSpammer)
            <x-button variant="danger" @click="$dispatch('open-modal', 'forever-delete-spammer')">Forever Delete</x-button>
        @endif
    </div>
</section>

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

<x-modal name="force-reset-password">
    <h2 class="text-lg font-bold">Kirim link reset password?</h2>
    <p class="mt-2 text-sm text-text-muted">Link akan dikirim ke {{ $user->email }} menggunakan email reset password standar.</p>
    <div class="mt-5 flex justify-end gap-2">
        <x-button variant="ghost" @click="$dispatch('close-modal', 'force-reset-password')">Batal</x-button>
        <form method="POST" action="{{ route('office.users.force-reset-password', $user) }}">
            @csrf
            <x-button type="submit">Kirim Link</x-button>
        </form>
    </div>
</x-modal>

@unless ($user->hasVerifiedEmail())
    <x-modal name="force-verify-email">
        <h2 class="text-lg font-bold">Verifikasi email customer?</h2>
        <p class="mt-2 text-sm text-text-muted">Email {{ $user->email }} akan ditandai terverifikasi tanpa mengirim email.</p>
        <div class="mt-5 flex justify-end gap-2">
            <x-button variant="ghost" @click="$dispatch('close-modal', 'force-verify-email')">Batal</x-button>
            <form method="POST" action="{{ route('office.users.force-verify-email', $user) }}">
                @csrf
                <x-button type="submit">Verifikasi Email</x-button>
            </form>
        </div>
    </x-modal>
@endunless
@endsection
