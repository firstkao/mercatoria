@extends('admin.layouts.app', ['title' => 'Edit Pengguna: ' . $user->displayName(), 'back' => route('admin.users.index')])

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel stack">
    @csrf
    @method('PUT')

    <label class="field">
        <span>Nama Lengkap</span>
        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" maxlength="255" required>
        @include('admin.partials.error', ['name' => 'full_name'])
    </label>

    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
        @include('admin.partials.error', ['name' => 'email'])
    </label>

    <label class="field">
        <span>No. WhatsApp</span>
        <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" maxlength="20">
        @include('admin.partials.error', ['name' => 'whatsapp'])
    </label>

    <label class="field">
        <span>Password Baru (opsional)</span>
        <input type="password" name="password" minlength="8">
        <small class="hint">Biarkan kosong kalau nggak mau ganti.</small>
        @include('admin.partials.error', ['name' => 'password'])
    </label>

    <label class="field">
        <span>Peran</span>
        <select name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>
                    {{ ucfirst($role->value) }}
                </option>
            @endforeach
        </select>
        @include('admin.partials.error', ['name' => 'role'])
    </label>

    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.users.show', $user) }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Perbarui</button>
    </div>
</form>

@unless ($user->anonymized_at)
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="danger-zone"
          onsubmit="return confirm('Yakin hapus pengguna ini? Riwayat order tetap tersimpan.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus Pengguna</button>
    </form>
@endunless
@endsection