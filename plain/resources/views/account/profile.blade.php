@extends('layouts.app', ['title' => 'Edit Profil'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Edit Profil</h1>
            <div class="account-head__meta">
                <span>Nama dan alamat dapat diubah. Tanggal lahir dan email terkunci.</span>
            </div>
        </header>

        @if (session('gatekeeper_error'))
            <div class="account-notice account-notice--danger" role="alert">
                {{ session('gatekeeper_error') }}
            </div>
        @endif

        @if (session('status'))
            <div class="account-notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('account.profile.update') }}" class="account-form" novalidate>
            @csrf
            @method('PUT')

            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Identitas</h2>
                </div>
                <div class="account-form__grid">
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
                </div>
            </div>

            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Alamat Pengiriman</h2>
                </div>
                <div class="account-form__grid">
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

                    <label class="field">
                        <span>Kode pos</span>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" inputmode="numeric" maxlength="5" required>
                        @include('partials.field-error', ['name' => 'postal_code'])
                    </label>

                    <label class="field field--full">
                        <span>Alamat lengkap</span>
                        <textarea name="street_address" rows="3" maxlength="500" required>{{ old('street_address', $user->street_address) }}</textarea>
                        @include('partials.field-error', ['name' => 'street_address'])
                    </label>
                </div>
            </div>

            <div class="account-section account-section--actions">
                <a href="{{ route('account.show') }}" class="account-btn account-btn--ghost">Batal</a>
                <button type="submit" class="account-btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
