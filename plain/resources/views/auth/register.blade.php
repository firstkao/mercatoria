@extends('layouts.app', ['title' => 'Daftar'])

@section('main_class', 'main--narrow')

@section('content')
<div class="account-page account-page--register">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Daftar Akun</h1>
            <div class="account-head__meta">
                <span>Hanya untuk pengguna dengan alamat dan nomor WhatsApp Indonesia.</span>
            </div>
        </header>

        <form method="POST" action="{{ route('register') }}" class="account-form" novalidate>
            @csrf

            {{-- ============ DATA DIRI ============ --}}
            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Data Diri</h2>
                </div>
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Nama Lengkap Sesuai Identitas</span>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" autocomplete="name" maxlength="100" required>
                        <small class="hint">Hanya huruf, minimal 2 kata. Nama asal-asalan atau nama karakter akan ditolak dan dapat diblokir.</small>
                        @include('partials.field-error', ['name' => 'full_name'])
                    </label>

                    <label class="field field--full">
                        <span>Tanggal Lahir</span>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now('Asia/Jakarta')->toDateString() }}" required data-birth-date>
                        <small class="hint">Tidak bisa diubah setelah mendaftar.</small>
                        @include('partials.field-error', ['name' => 'birth_date'])
                    </label>

                    <label class="check field--full" data-parental-consent @unless (old('parental_consent') || $errors->has('parental_consent')) hidden @endunless>
                        <input type="checkbox" name="parental_consent" value="1" @checked(old('parental_consent'))>
                        <span>Saya berusia di bawah 18 tahun dan sudah mendapat persetujuan orang tua/wali.</span>
                    </label>
                    @include('partials.field-error', ['name' => 'parental_consent'])
                </div>
            </div>

            {{-- ============ ALAMAT ============ --}}
            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Alamat di Indonesia</h2>
                </div>
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Provinsi</span>
                        <select name="province" required>
                            <option value="">Pilih provinsi</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province }}" @selected(old('province') === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                        @include('partials.field-error', ['name' => 'province'])
                    </label>

                    <label class="field">
                        <span>Kota/Kabupaten</span>
                        <input type="text" name="city" value="{{ old('city') }}" maxlength="100" required>
                        @include('partials.field-error', ['name' => 'city'])
                    </label>

                    <label class="field">
                        <span>Kecamatan</span>
                        <input type="text" name="district" value="{{ old('district') }}" maxlength="100" required>
                        @include('partials.field-error', ['name' => 'district'])
                    </label>

                    <label class="field field--full">
                        <span>Alamat Lengkap</span>
                        <textarea name="street_address" rows="3" maxlength="500" required>{{ old('street_address') }}</textarea>
                        @include('partials.field-error', ['name' => 'street_address'])
                    </label>

                    <label class="field">
                        <span>Kode Pos</span>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" maxlength="5" autocomplete="postal-code" required>
                        @include('partials.field-error', ['name' => 'postal_code'])
                    </label>
                </div>
            </div>

            {{-- ============ KONTAK & AKUN ============ --}}
            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Kontak &amp; Akun</h2>
                </div>
                <div class="account-form__grid">
                    <label class="field field--full">
                        <span>Nomor WhatsApp</span>
                        <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                        @include('partials.field-error', ['name' => 'whatsapp'])
                    </label>

                    <label class="field field--full">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        @include('partials.field-error', ['name' => 'email'])
                    </label>

                    <label class="field">
                        <span>Kata Sandi</span>
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
                        <small class="hint">Minimal 8 karakter.</small>
                        @include('partials.field-error', ['name' => 'password'])
                    </label>

                    <label class="field">
                        <span>Ulangi Kata Sandi</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>

                    <label class="field field--full">
                        <span>Kode Undangan (opsional)</span>
                        <input type="text" name="referral_code" value="{{ old('referral_code', $prefilledReferral ?? '') }}"
                               maxlength="20" placeholder="Contoh: ABCD1234" style="text-transform: uppercase;">
                        <small class="hint">Punya kode dari teman? Isi di sini supaya kalian berdua dapat bonus koin setelah pesanan pertama kamu selesai.</small>
                        @include('partials.field-error', ['name' => 'referral_code'])
                    </label>
                </div>
            </div>

            {{-- ============ PERSETUJUAN ============ --}}
            <div class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Persetujuan</h2>
                </div>

                <div class="account-form__stack">
                    <div class="account-form__notice">
                        Akun baru hanya bisa membuka 10 detail produk dan terhapus otomatis 30 hari setelah mendaftar jika belum ada pembayaran.
                        Email dan nomor WhatsApp yang sama maksimal 3 kali mendaftar.
                    </div>

                    <label class="check">
                        <input type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms'))>
                        <span>Saya menyetujui <button type="button" class="link-button" data-open-dialog="terms-dialog">Syarat &amp; Ketentuan</button>.</span>
                    </label>
                    @include('partials.field-error', ['name' => 'accept_terms'])

                    <label class="check">
                        <input type="checkbox" name="accept_privacy" value="1" @checked(old('accept_privacy'))>
                        <span>Saya menyetujui <button type="button" class="link-button" data-open-dialog="privacy-dialog">Kebijakan Privasi</button>.</span>
                    </label>
                    @include('partials.field-error', ['name' => 'accept_privacy'])

                    <div class="account-form__turnstile">
                        @include('partials.turnstile')
                    </div>
                </div>
            </div>

            <div class="account-section account-section--actions">
                <button type="submit" class="account-btn account-btn--block">Daftar</button>
            </div>
        </form>

        <div class="account-auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </div>
    </div>
</div>

@include('partials.legal-dialog', ['id' => 'terms-dialog', 'title' => 'Syarat & Ketentuan', 'html' => $termsHtml, 'page' => 'syarat-dan-ketentuan'])
@include('partials.legal-dialog', ['id' => 'privacy-dialog', 'title' => 'Kebijakan Privasi', 'html' => $privacyHtml, 'page' => 'kebijakan-privasi'])
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-open-dialog]').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById(button.dataset.openDialog).showModal();
            });
        });

        (function () {
            var input = document.querySelector('[data-birth-date]');
            var consent = document.querySelector('[data-parental-consent]');

            function toggleConsent() {
                if (!input.value) return;
                var birth = new Date(input.value + 'T00:00:00');
                var adult = new Date(birth.getFullYear() + 18, birth.getMonth(), birth.getDate());
                consent.hidden = adult <= new Date();
            }

            input.addEventListener('change', toggleConsent);
            toggleConsent();
        })();
    </script>
@endpush
