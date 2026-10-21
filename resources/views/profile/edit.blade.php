@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class='mx-auto max-w-4xl px-4 py-8'>
    <x-dashboard-tabs />
    <h1 class='mt-6 text-2xl font-bold text-navy-500'>Edit Profil &amp; Alamat Pengiriman</h1>
    @if (session('success'))<p role='status' class='mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800'>{{ session('success') }}</p>@endif
    @if ($errors->any())<p role='alert' class='mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800'>{{ $errors->first() }}</p>@endif

    <form method='POST' action='{{ route('profile.edit.update') }}' class='mt-5 space-y-6 rounded-xl border border-border bg-white p-5 sm:p-7'>
        @csrf
        @method('PUT')
        <div class='grid gap-4 sm:grid-cols-2'>
            <div><label for='name' class='mb-1 block text-sm font-medium'>Nama Lengkap *</label><input id='name' name='name' value='{{ old('name', $user->name) }}' required maxlength='100' class='w-full rounded-lg border-border'>@error('name')<p class='mt-1 text-xs text-red-600'>{{ $message }}</p>@enderror</div>
            <div><label for='no_hp' class='mb-1 block text-sm font-medium'>No HP (WhatsApp) *</label><input id='no_hp' name='no_hp' type='tel' value='{{ old('no_hp', $user->no_hp) }}' required class='w-full rounded-lg border-border'><p class='mt-1 text-xs text-text-muted'>Perubahan No HP divalidasi unik &amp; dicek ke daftar Spammer.</p>@error('no_hp')<p class='mt-1 text-xs text-red-600'>{{ $message }}</p>@enderror</div>
            <div class='sm:col-span-2'><label for='email' class='mb-1 block text-sm font-medium'>Email * <span class='font-normal text-text-muted'>(Ubah → Wajib Verifikasi Ulang)</span></label><input id='email' name='email' type='email' value='{{ old('email', $user->email) }}' required maxlength='150' class='w-full rounded-lg border-border'><p class='mt-1 text-xs text-text-muted'>Mengubah email akan meminta verifikasi ulang.</p>@error('email')<p class='mt-1 text-xs text-red-600'>{{ $message }}</p>@enderror</div>
            <div><label class='mb-1 block text-sm font-medium'>Tanggal Lahir 🔒</label><input value='{{ $user->tgl_lahir ? $user->tgl_lahir->format('d / m / Y').' (Tidak bisa diubah)' : 'Belum diisi' }}' disabled class='w-full rounded-lg border-border bg-surface-2 text-text-muted'><p class='mt-1 text-xs text-text-muted'>Terkunci permanen.</p></div>
            <div><label class='mb-1 block text-sm font-medium'>Kode Undangan Digunakan 🔒</label><input value='{{ $user->invited_by_code ? $user->invited_by_code.' (Terkunci)' : '— (Terkunci)' }}' disabled class='w-full rounded-lg border-border bg-surface-2 text-text-muted'><p class='mt-1 text-xs text-text-muted'>Tidak dapat diubah.</p></div>
        </div>

        <fieldset class='space-y-4 border-t border-border pt-5'>
            <legend class='px-1 text-lg font-semibold'>Alamat Pengiriman</legend>
            <div class='grid gap-4 sm:grid-cols-2'>
                <div><label for='provinsi' class='mb-1 block text-sm font-medium'>Provinsi *</label><select id='provinsi' name='provinsi' required class='w-full rounded-lg border-border'><option value=''>Pilih Provinsi</option>@foreach ($provinces as $province)<option value='{{ $province }}' @selected(old('provinsi', $user->provinsi) === $province)>{{ $province }}</option>@endforeach</select>@error('provinsi')<p class='mt-1 text-xs text-red-600'>{{ $message }}</p>@enderror</div>
                <div><label for='kota' class='mb-1 block text-sm font-medium'>Kota/Kabupaten *</label><input id='kota' name='kota' value='{{ old('kota', $user->kota) }}' required class='w-full rounded-lg border-border'></div>
                <div><label for='kecamatan' class='mb-1 block text-sm font-medium'>Kecamatan *</label><input id='kecamatan' name='kecamatan' value='{{ old('kecamatan', $user->kecamatan) }}' required class='w-full rounded-lg border-border'></div>
                <div><label for='kode_pos' class='mb-1 block text-sm font-medium'>Kode Pos *</label><input id='kode_pos' name='kode_pos' value='{{ old('kode_pos', $user->kode_pos) }}' required maxlength='10' class='w-full rounded-lg border-border'></div>
                <div class='sm:col-span-2'><label for='alamat_lengkap' class='mb-1 block text-sm font-medium'>Alamat Lengkap *</label><textarea id='alamat_lengkap' name='alamat_lengkap' rows='3' required maxlength='500' class='w-full rounded-lg border-border'>{{ old('alamat_lengkap', $user->alamat_lengkap) }}</textarea></div>
            </div>
        </fieldset>

        <fieldset class='space-y-4 border-t border-border pt-5'>
            <legend class='px-1 text-lg font-semibold'>Ubah Kata Sandi <span class='text-sm font-normal text-text-muted'>(Opsional)</span></legend>
            <div class='grid gap-4 sm:grid-cols-3'>
                <div><label for='current_password' class='mb-1 block text-sm font-medium'>Password Lama</label><input id='current_password' name='current_password' type='password' autocomplete='current-password' class='w-full rounded-lg border-border'></div>
                <div><label for='password' class='mb-1 block text-sm font-medium'>Password Baru</label><input id='password' name='password' type='password' autocomplete='new-password' class='w-full rounded-lg border-border'></div>
                <div><label for='password_confirmation' class='mb-1 block text-sm font-medium'>Konfirmasi Kata Sandi</label><input id='password_confirmation' name='password_confirmation' type='password' autocomplete='new-password' class='w-full rounded-lg border-border'></div>
            </div>
        </fieldset>
        <div class='flex justify-end'><button type='submit' class='rounded-lg bg-[#4FC3F7] px-5 py-3 font-semibold text-white hover:brightness-95'>Simpan Profil</button></div>
    </form>
</div>
@endsection
