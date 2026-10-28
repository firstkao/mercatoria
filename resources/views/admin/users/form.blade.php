@extends('admin.layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Tambah User')

@section('content')
    <h1 class="text-xl font-semibold">{{ $user->exists ? 'Edit: '.$user->name : 'Tambah User (Inject)' }}</h1>

    <form method="POST"
          action="{{ $user->exists ? route('office.users.update', $user) : route('office.users.store') }}"
          class="mt-4 space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Password {{ $user->exists ? '(kosongkan bila tetap)' : '*' }}</label>
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }} class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Role</label>
                <select name="role" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="customer" @selected(old('role', $user->role ?: 'customer') === 'customer')>customer</option>
                    <option value="admin" @selected(old('role') === 'admin' || $user->role === 'admin')>admin</option>
                </select>
            </div>

            @unless ($user->exists)
                <div>
                    <label class="block text-sm font-medium">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">No HP <span class="text-red-500">*</span></label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Provinsi <span class="text-red-500">*</span></label>
                    <input type="text" name="provinsi" value="{{ old('provinsi') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kota <span class="text-red-500">*</span></label>
                    <input type="text" name="kota" value="{{ old('kota') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kecamatan <span class="text-red-500">*</span></label>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Kode Pos <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_pos" value="{{ old('kode_pos') }}" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" rows="2" required class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">{{ old('alamat') }}</textarea>
                </div>
            @endunless
        </div>

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">
            {{ $user->exists ? 'SIMPAN PERUBAHAN' : 'SIMPAN USER' }}
        </button>
    </form>
@endsection
