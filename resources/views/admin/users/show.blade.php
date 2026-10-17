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

<section class="mt-5 rounded-xl border border-border bg-white p-5">
    <h2 class="font-semibold">Bantuan aktivasi</h2>
    <p class="mt-1 text-sm text-text-muted">Aksi admin dicatat pada log. Konfirmasi akan diminta sebelum dijalankan.</p>
    <div class="mt-4 flex flex-wrap gap-3">
        <x-button variant="ghost" @click="$dispatch('open-modal', 'force-reset-password')">Force Reset Password</x-button>
        @unless ($user->hasVerifiedEmail())
            <x-button variant="ghost" @click="$dispatch('open-modal', 'force-verify-email')">Force Verify Email</x-button>
        @endunless
    </div>
</section>

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
