@extends('layouts.app', ['title' => 'Daftar'])

@section('content')
    <section class="card">
        <h1>Daftar akun</h1>
        <p class="muted">Hanya untuk pengguna dengan alamat dan nomor WhatsApp Indonesia.</p>

        <form method="POST" action="{{ route('register') }}" class="form" novalidate>
            @csrf

            <fieldset>
                <legend>Data diri</legend>

                <label class="field">
                    <span>Nama lengkap sesuai identitas</span>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" autocomplete="name" maxlength="100" required>
                    <small class="hint">Hanya huruf, minimal 2 kata. Nama asal-asalan atau nama karakter akan ditolak dan dapat diblokir.</small>
                    @include('partials.field-error', ['name' => 'full_name'])
                </label>

                <label class="field">
                    <span>Tanggal lahir</span>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now('Asia/Jakarta')->toDateString() }}" required data-birth-date>
                    <small class="hint">Tidak bisa diubah setelah mendaftar.</small>
                    @include('partials.field-error', ['name' => 'birth_date'])
                </label>

                <label class="check" data-parental-consent @unless (old('parental_consent') || $errors->has('parental_consent')) hidden @endunless>
                    <input type="checkbox" name="parental_consent" value="1" @checked(old('parental_consent'))>
                    <span>Saya berusia di bawah 18 tahun dan sudah mendapat persetujuan orang tua/wali.</span>
                </label>
                @include('partials.field-error', ['name' => 'parental_consent'])
            </fieldset>

            <fieldset>
                <legend>Alamat di Indonesia</legend>

                <label class="field">
                    <span>Provinsi</span>
                    <select name="province" required>
                        <option value="">Pilih provinsi</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province }}" @selected(old('province') === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'province'])
                </label>

                <div class="field-row">
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
                </div>

                <label class="field">
                    <span>Alamat lengkap</span>
                    <textarea name="street_address" rows="3" maxlength="500" required>{{ old('street_address') }}</textarea>
                    @include('partials.field-error', ['name' => 'street_address'])
                </label>

                <label class="field field--short">
                    <span>Kode pos</span>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" maxlength="5" autocomplete="postal-code" required>
                    @include('partials.field-error', ['name' => 'postal_code'])
                </label>
            </fieldset>

            <fieldset>
                <legend>Kontak &amp; akun</legend>

                <label class="field">
                    <span>Nomor WhatsApp</span>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                    @include('partials.field-error', ['name' => 'whatsapp'])
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @include('partials.field-error', ['name' => 'email'])
                </label>

                <div class="field-row">
                    <label class="field">
                        <span>Kata sandi</span>
                        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
                        <small class="hint">Minimal 8 karakter.</small>
                        @include('partials.field-error', ['name' => 'password'])
                    </label>
                    <label class="field">
                        <span>Ulangi kata sandi</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                </div>
            </fieldset>

            <div class="notice">
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

            @include('partials.turnstile')

            <button type="submit" class="button button--block">Daftar</button>
        </form>

        <p class="muted center">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </section>

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
                if (!input.value) {
                    return;
                }
                var birth = new Date(input.value + 'T00:00:00');
                var adult = new Date(birth.getFullYear() + 18, birth.getMonth(), birth.getDate());
                consent.hidden = adult <= new Date();
            }

            input.addEventListener('change', toggleConsent);
            toggleConsent();
        })();
    </script>
@endpush