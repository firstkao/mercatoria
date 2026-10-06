@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.general.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>Kontak & Sosial Media</h2>
        <p class="hint">Info ini tampil di footer toko dan halaman legal. Kosongkan untuk menyembunyikan.</p>

        <div class="field-row">
            <label class="field">
                <span>Email CS</span>
                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" maxlength="150" placeholder="cs@mercatoria.id">
                @include('admin.partials.error', ['name' => 'contact_email'])
            </label>
            <label class="field">
                <span>WhatsApp CS</span>
                <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact_whatsapp']) }}" maxlength="30" placeholder="+62 812-1968-3709">
                @include('admin.partials.error', ['name' => 'contact_whatsapp'])
            </label>
        </div>

        <label class="field">
            <span>Jam operasional (opsional)</span>
            <input type="text" name="contact_hours" value="{{ old('contact_hours', $settings['contact_hours']) }}" maxlength="150" placeholder="Senin–Jumat, 09:00–17:00 WIB">
            @include('admin.partials.error', ['name' => 'contact_hours'])
        </label>

        <label class="field">
            <span>Alamat toko (opsional)</span>
            <textarea name="store_address" rows="3" maxlength="500" placeholder="Alamat lengkap...">{{ old('store_address', $settings['store_address']) }}</textarea>
            @include('admin.partials.error', ['name' => 'store_address'])
        </label>

        <h3 style="margin-top:20px;">Sosial Media (dinamis)</h3>
        <p class="hint">Kelola dari sini: tambah/hapus/ubah urutan. Yang aktif tampil di header &amp; footer toko. Kosongkan URL untuk menyembunyikan ikon. Baris dengan ikon <strong>Marketplace</strong> (Toco/Shopee/Tokopedia/TikTok Shop) otomatis dirender sebagai tombol teks di footer, bukan ikon SVG.</p>

        @php
            $builtInIcons = \App\Models\SocialMedia::BUILT_IN_ICONS;
            $marketplaceIcons = \App\Support\PublicMarketplace::ICON_KEYS;
        @endphp

        <div id="socials-list" class="stack">
            @foreach ($socialMedias as $i => $sm)
                <div class="field-row social-row" style="align-items:end;">
                    <input type="hidden" name="socials[{{ $i }}][id]" value="{{ $sm->id }}">
                    <label class="field">
                        <span>Nama</span>
                        <input type="text" name="socials[{{ $i }}][name]" value="{{ old("socials.$i.name", $sm->name) }}" maxlength="100" placeholder="Instagram">
                    </label>
                    <label class="field">
                        <span>URL</span>
                        <input type="url" name="socials[{{ $i }}][url]" value="{{ old("socials.$i.url", $sm->url) }}" maxlength="255" placeholder="https://instagram.com/mercatoria.id">
                    </label>
                    <label class="field">
                        <span>Ikon</span>
                        <select name="socials[{{ $i }}][icon_key]" data-built-in="{{ implode(',', $builtInIcons) }}">
                            <option value="">— custom/teks —</option>
                            <optgroup label="Sosial Media">
                                @foreach ($builtInIcons as $key)
                                    <option value="{{ $key }}" @selected(old("socials.$i.icon_key", $sm->icon_key) === $key)>{{ ucfirst($key) }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Marketplace Footer">
                                @foreach ($marketplaceIcons as $key)
                                    <option value="{{ $key }}" @selected(old("socials.$i.icon_key", $sm->icon_key) === $key)>{{ ucfirst($key) }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </label>
                    <label class="field icon-url-field">
                        <span>Icon URL (opsional)</span>
                        <input type="url" name="socials[{{ $i }}][icon_url]" value="{{ old("socials.$i.icon_url", $sm->icon_url) }}" maxlength="500" placeholder="https://.../icon.png">
                        <small style="font-size:11px;opacity:.7;line-height:1.3;display:block;margin-top:2px;">Untuk platform custom. Akan diabaikan jika ikon sosial media bawaan dipilih.</small>
                    </label>
                    <label class="field" style="max-width:90px;">
                        <span>Urutan</span>
                        <input type="number" name="socials[{{ $i }}][sort_order]" value="{{ old("socials.$i.sort_order", $sm->sort_order) }}" min="0" max="999">
                    </label>
                    <label class="field" style="max-width:110px;">
                        <span>Aktif</span>
                        <input type="hidden" name="socials[{{ $i }}][is_active]" value="0">
                        <input type="checkbox" name="socials[{{ $i }}][is_active]" value="1" @checked(old("socials.$i.is_active", $sm->is_active))>
                    </label>
                    <label class="field" style="max-width:110px;">
                        <span>Hapus</span>
                        <input type="checkbox" name="socials[{{ $i }}][delete]" value="1">
                    </label>
                </div>
            @endforeach
        </div>

        <template id="social-row-template">
            <div class="field-row social-row" style="align-items:end;">
                <input type="hidden" name="socials[__INDEX__][id]" value="">
                <label class="field"><span>Nama</span><input type="text" name="socials[__INDEX__][name]" maxlength="100" placeholder="Threads"></label>
                <label class="field"><span>URL</span><input type="url" name="socials[__INDEX__][url]" maxlength="255" placeholder="https://..."></label>
                <label class="field"><span>Ikon</span>
                    <select name="socials[__INDEX__][icon_key]" data-built-in="{{ implode(',', $builtInIcons) }}">
                        <option value="">— custom/teks —</option>
                        <optgroup label="Sosial Media">
                            @foreach ($builtInIcons as $key)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach
                        </optgroup>
                        <optgroup label="Marketplace Footer">
                            @foreach ($marketplaceIcons as $key)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach
                        </optgroup>
                    </select>
                </label>
                <label class="field icon-url-field">
                    <span>Icon URL (opsional)</span>
                    <input type="url" name="socials[__INDEX__][icon_url]" maxlength="500" placeholder="https://.../icon.png">
                    <small style="font-size:11px;opacity:.7;line-height:1.3;display:block;margin-top:2px;">Untuk platform custom. Akan diabaikan jika ikon sosial media bawaan dipilih.</small>
                </label>
                <label class="field" style="max-width:90px;"><span>Urutan</span><input type="number" name="socials[__INDEX__][sort_order]" value="99" min="0" max="999"></label>
                <label class="field" style="max-width:110px;"><span>Aktif</span><input type="hidden" name="socials[__INDEX__][is_active]" value="0"><input type="checkbox" name="socials[__INDEX__][is_active]" value="1" checked></label>
                <label class="field" style="max-width:110px;"><span>Hapus</span><input type="checkbox" name="socials[__INDEX__][delete]" value="1"></label>
            </div>
        </template>

        <button type="button" class="btn btn--ghost" id="add-social">+ Tambah Sosial Media</button>

        <script>
            (function () {
                // BUG FIX UX: Sembunyikan field Icon URL ketika ikon SVG bawaan
                // (Instagram/Facebook/X/Threads/WhatsApp) dipilih. Untuk ikon
                // Marketplace (Toco/Shopee/Tokopedia/TikTokShop), field TETAP
                // DITAMPILKAN karena baris marketplace tidak punya SVG inline.
                function toggleIconUrl(row) {
                    if (!row) return;
                    var sel = row.querySelector('select[name*="[icon_key]"]');
                    var urlField = row.querySelector('.icon-url-field');
                    if (!sel || !urlField) return;

                    var builtIn = (sel.dataset.builtIn || '').split(',').filter(Boolean);
                    var urlInput = urlField.querySelector('input');
                    var isBuiltInSvg = builtIn.indexOf(sel.value) !== -1;

                    urlField.style.display = isBuiltInSvg ? 'none' : '';
                    if (isBuiltInSvg && urlInput) {
                        urlInput.value = '';
                    }
                }

                function initRow(row) {
                    if (!row) return;
                    var sel = row.querySelector('select[name*="[icon_key]"]');
                    if (!sel) return;
                    toggleIconUrl(row);
                    sel.addEventListener('change', function () { toggleIconUrl(row); });
                }

                // Init semua baris existing saat halaman dimuat.
                document.querySelectorAll('#socials-list .social-row').forEach(initRow);

                // Tombol tambah baris baru.
                var addBtn = document.getElementById('add-social');
                if (addBtn) {
                    addBtn.addEventListener('click', function () {
                        var list = document.getElementById('socials-list');
                        var tpl = document.getElementById('social-row-template').innerHTML;
                        var index = Date.now(); // indeks unik utk array POST
                        list.insertAdjacentHTML('beforeend', tpl.replaceAll('__INDEX__', index));
                        initRow(list.lastElementChild);
                    });
                }
            })();
        </script>

        <h3 style="margin-top:20px;">Widget WhatsApp</h3>
        <div class="field-row">
            <label class="field">
                <span>Aktifkan tombol WhatsApp</span>
                <input type="hidden" name="wa_widget_enabled" value="0">
                <input type="checkbox" name="wa_widget_enabled" value="1" @checked(($settings['wa_widget_enabled'] ?? '0') === '1')>
            </label>
            <label class="field">
                <span>Sapaan WhatsApp</span>
                <input type="text" name="wa_widget_greeting" value="{{ $settings['wa_widget_greeting'] ?? '' }}" maxlength="500" placeholder="Halo! Ada yang bisa kami bantu?">
            </label>
        </div>

        <h3 style="margin-top:20px;">Pengingat Keranjang (Batch 31)</h3>
        <div class="field-row">
            <label class="field">
                <span>Reminder #1 (jam setelah ditinggal)</span>
                <input type="number" name="cart_reminder_1_hours" value="{{ $settings['cart_reminder_1_hours'] ?? '' }}" min="1" max="72" placeholder="4">
            </label>
            <label class="field">
                <span>Reminder #2 (jam setelah ditinggal)</span>
                <input type="number" name="cart_reminder_2_hours" value="{{ $settings['cart_reminder_2_hours'] ?? '' }}" min="2" max="168" placeholder="24">
            </label>
        </div>

        <h3 style="margin-top:20px;">Best Seller (Batch 30)</h3>
        <div class="field-row">
            <label class="field">
                <span>Periode (hari)</span>
                <select name="best_seller_period_days">
                    @foreach ([30, 90, 180, 365, 730] as $d)
                        <option value="{{ $d }}" @selected(($settings['best_seller_period_days'] ?? '') == $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Jumlah produk</span>
                <input type="number" name="best_seller_limit" value="{{ $settings['best_seller_limit'] ?? '' }}" min="4" max="20" placeholder="8">
            </label>
            <label class="field">
                <span>Minimal penjualan</span>
                <input type="number" name="best_seller_min_sales" value="{{ $settings['best_seller_min_sales'] ?? '' }}" min="1" max="100" placeholder="3">
            </label>
        </div>

        <h3 style="margin-top:20px;">Verifikasi Email &amp; Pemeliharaan</h3>
        <div class="field-row">
            <label class="field">
                <span>Wajib verifikasi email saat daftar</span>
                <input type="hidden" name="email_verification_enabled" value="0">
                <input type="checkbox" name="email_verification_enabled" value="1" @checked(($settings['email_verification_enabled'] ?? '1') === '1')>
            </label>
            <label class="field">
                <span>Mode pemeliharaan aktif</span>
                <input type="hidden" name="maintenance_enabled" value="0">
                <input type="checkbox" name="maintenance_enabled" value="1" @checked(($settings['maintenance_enabled'] ?? '0') === '1')>
            </label>
        </div>
        <label class="field">
            <span>Pesan pemeliharaan</span>
            <input type="text" name="maintenance_message" value="{{ $settings['maintenance_message'] ?? '' }}" maxlength="500" placeholder="Kami sedang melakukan pemeliharaan. Coba lagi sebentar lagi.">
        </label>
        <label class="field">
            <span>IP bypass pemeliharaan (pisahkan dengan koma)</span>
            <input type="text" name="maintenance_bypass_ips" value="{{ $settings['maintenance_bypass_ips'] ?? '' }}" maxlength="500" placeholder="103.10.10.1, 103.20.20.2">
        </label>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan pengaturan</button>
        </div>
    </form>
@endsection
