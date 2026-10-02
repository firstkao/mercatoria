@extends('layouts.app', ['title' => 'Edit Profil'])

@section('content')
    <section class="card">
        <p class="eyebrow">Akun saya</p>
        <h1>Edit Profil</h1>
        <p class="muted">Nama dan alamat dapat diubah. Tanggal lahir dan email tidak dapat diubah sendiri.</p>

        {{-- BUG FIX: dulu redirect sukses menunjuk dashboard sehingga notif
             "berhasil diperbarui" tak pernah terlihat di sini. Sekarang
             controller pakai back() + blok status ini menampilkannya. --}}
        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('account.profile.update') }}" class="form" novalidate>
            @csrf
            @method('PUT')

            <label class="field">
                <span>Nama lengkap</span>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" maxlength="100" required>
                <small class="hint">Hanya huruf, minimal 2 kata.</small>
                @include('partials.field-error', ['name' => 'full_name'])
            </label>

            <label class="field">
                <span>Nomor WhatsApp</span>
                <input type="tel" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="628xxxxxxxxxx" required>
                @include('partials.field-error', ['name' => 'whatsapp'])
            </label>

            <label class="field">
                <span>Provinsi</span>
                <select name="province" required>
                    <option value="">Pilih provinsi</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(old('province', $user->province) === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'province'])
            </label>

            <div class="field-row">
                <label class="field">
                    <span>Kota/Kabupaten</span>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" maxlength="100" required>
                    @include('partials.field-error', ['name' => 'city'])
                </label>
                <label class="field">
                    <span>Kecamatan</span>
                    <input type="text" name="district" value="{{ old('district', $user->district) }}" maxlength="100" required>
                    @include('partials.field-error', ['name' => 'district'])
                </label>
            </div>

            <label class="field">
                <span>Alamat lengkap</span>
                <textarea name="street_address" rows="3" maxlength="500" required>{{ old('street_address', $user->street_address) }}</textarea>
                @include('partials.field-error', ['name' => 'street_address'])
            </label>

            <label class="field field--short">
                <span>Kode pos</span>
                <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" inputmode="numeric" maxlength="5" required>
                @include('partials.field-error', ['name' => 'postal_code'])
            </label>

            <div style="display:flex;gap:10px;">
                <a href="{{ route('account.show') }}" class="button button--small" style="background:#e5e7eb;color:#111;">Batal</a>
                <button type="submit" class="button button--small">Simpan</button>
            </div>
        </form>
    </section>
@endsection