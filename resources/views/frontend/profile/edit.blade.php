@extends('layouts.app')

@section('title', 'Edit Profil — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Edit Profil</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin Saya</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Edit Profil</a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('name')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('email')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">No HP (WA) <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('no_hp')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Tanggal Lahir <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="{{ $user->tgl_lahir?->format('d/m/Y') }}" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Undangan <span class="text-ink/45">(Tidak dapat diubah)</span></label>
                <input type="text" value="{{ $user->referrer?->referral_code ?? '-' }}" disabled
                       class="mt-2 w-full rounded-lg border border-rule bg-paper px-3.5 py-2.5 text-sm text-ink/55">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Provinsi <span class="text-danger">*</span></label>
                <input type="text" name="provinsi" value="{{ old('provinsi', $user->provinsi) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kota / Kabupaten <span class="text-danger">*</span></label>
                <input type="text" name="kota" value="{{ old('kota', $user->kota) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kecamatan <span class="text-danger">*</span></label>
                <input type="text" name="kecamatan" value="{{ old('kecamatan', $user->kecamatan) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kode Pos <span class="text-danger">*</span></label>
                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->kode_pos) }}" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
        </div>

        <div>
            <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" rows="2" required
                      class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.08em] text-white transition-colors hover:bg-ink">SIMPAN PROFIL</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-4 rounded-xl border border-rule bg-white p-6">
        @csrf
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Ganti Kata Sandi</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Lama <span class="text-danger">*</span></label>
                <input type="password" name="current_password" required
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('current_password')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
                @error('password')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="mt-2 w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            </div>
        </div>

        <button type="submit" class="rounded-lg border border-rule px-6 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-ink">GANTI KATA SANDI</button>
    </form>
@endsection
