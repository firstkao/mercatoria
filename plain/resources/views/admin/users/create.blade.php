@extends('admin.layouts.app', ['title' => 'Tambah Pengguna', 'back' => route('admin.users.index')])

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="panel stack">
    @csrf

    <label class="field">
        <span>Nama Lengkap</span>
        <input type="text" name="full_name" value="{{ old('full_name') }}" maxlength="255" required>
        @include('admin.partials.error', ['name' => 'full_name'])
    </label>

    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="{{ old('email') }}" maxlength="255" required>
        @include('admin.partials.error', ['name' => 'email'])
    </label>

    <label class="field">
        <span>No. WhatsApp (opsional)</span>
        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" maxlength="20" placeholder="628xxxxxxxxxx">
        @include('admin.partials.error', ['name' => 'whatsapp'])
    </label>

    <label class="field">
        <span>Password</span>
        <input type="password" name="password" minlength="8" required>
        <small class="hint">Minimal 8 karakter.</small>
        @include('admin.partials.error', ['name' => 'password'])
    </label>

    <label class="field">
        <span>Peran</span>
        <select name="role" required>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>
                    {{ ucfirst($role->value) }}
                </option>
            @endforeach
        </select>
        @include('admin.partials.error', ['name' => 'role'])
    </label>

    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.users.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn--primary">Simpan</button>
    </div>
</form>
@endsection