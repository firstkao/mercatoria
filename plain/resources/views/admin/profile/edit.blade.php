@extends('admin.layouts.app', ['title' => 'Profil Admin'])

@section('content')
    <div class="form-grid">
        <div class="form-grid__main stack">

            {{-- Data Diri --}}
            <form method="POST" action="{{ route('admin.profile.update') }}" class="panel stack">
                @csrf
                @method('PUT')
                <h2>Data Diri</h2>

                <label class="field">
                    <span>Nama</span>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" maxlength="100" required>
                    @include('admin.partials.error', ['name' => 'name'])
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" maxlength="150" required>
                    @include('admin.partials.error', ['name' => 'email'])
                </label>

                <div>
                    <button type="submit" class="btn btn--primary">Simpan</button>
                </div>
            </form>

            {{-- Ganti Password --}}
            <form method="POST" action="{{ route('admin.profile.password') }}" class="panel stack">
                @csrf
                @method('PUT')
                <h2>Ganti Kata Sandi</h2>
                <p class="hint">Minimal 10 karakter. Setelah berhasil, kamu tetap login.</p>

                <label class="field">
                    <span>Kata sandi saat ini</span>
                    <input type="password" name="current_password" required autocomplete="current-password">
                    @include('admin.partials.error', ['name' => 'current_password'])
                </label>

                <label class="field">
                    <span>Kata sandi baru</span>
                    <input type="password" name="password" required autocomplete="new-password">
                    @include('admin.partials.error', ['name' => 'password'])
                </label>

                <label class="field">
                    <span>Ulangi kata sandi baru</span>
                    <input type="password" name="password_confirmation" required autocomplete="new-password">
                </label>

                <div>
                    <button type="submit" class="btn btn--primary">Ganti Kata Sandi</button>
                </div>
            </form>
        </div>

        <aside class="form-grid__side">
            <section class="panel stack">
                <h2>Info Akun</h2>
                <dl class="deflist">
                    <div><dt>Login terakhir</dt><dd>{{ $admin->updated_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') ?? '—' }}</dd></div>
                    <div><dt>ID Admin</dt><dd class="mono">#{{ $admin->id }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection