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
        <p class="hint">Kelola dari sini: tambah/hapus/ubah urutan. Yang aktif tampil di header &amp; footer toko. Kosongkan URL untuk menyembunyikan ikon.</p>

        @php($iconKeys = \App\Models\SocialMedia::BUILT_IN_ICONS)

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
                        <span>Ikon bawaan</span>
                        <select name="socials[{{ $i }}][icon_key]">
                            <option value="">— custom/teks —</option>
                            @foreach ($iconKeys as $key)
                                <option value="{{ $key }}" @selected(old("socials.$i.icon_key", $sm->icon_key) === $key)>{{ ucfirst($key) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span>Icon URL (opsional)</span>
                        <input type="url" name="socials[{{ $i }}][icon_url]" value="{{ old("socials.$i.icon_url", $sm->icon_url) }}" maxlength="500" placeholder="https://.../icon.png">
                    </label>
                    <label class="field" style="max-width:90px;">
                        <span>Urutan</span>
                        <input type="number" name="socials[{{ $i }}][sort_order]" value="{{ old("socials.$i.sort_order", $sm->sort_order) }}" min="0" max="999">
                    </label>
                    <label class="field" style="max-width:110px;">
                        <span>Aktif</span>
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
                <label class="field"><span>Ikon bawaan</span>
                    <select name="socials[__INDEX__][icon_key]">
                        <option value="">— custom/teks —</option>
                        @foreach ($iconKeys as $key)<option value="{{ $key }}">{{ ucfirst($key) }}</option>@endforeach
                    </select>
                </label>
                <label class="field"><span>Icon URL (opsional)</span><input type="url" name="socials[__INDEX__][icon_url]" maxlength="500"></label>
                <label class="field" style="max-width:90px;"><span>Urutan</span><input type="number" name="socials[__INDEX__][sort_order]" value="99" min="0" max="999"></label>
                <label class="field" style="max-width:110px;"><span>Aktif</span><input type="checkbox" name="socials[__INDEX__][is_active]" value="1" checked></label>
                <label class="field" style="max-width:110px;"><span>Hapus</span><input type="checkbox" name="socials[__INDEX__][delete]" value="1"></label>
            </div>
        </template>

        <button type="button" class="btn btn--ghost" id="add-social">+ Tambah Sosial Media</button>
        <script>
            document.getElementById('add-social').addEventListener('click', function () {
                var list = document.getElementById('socials-list');
                var tpl = document.getElementById('social-row-template').innerHTML;
                var index = Date.now(); // indeks unik utk array POST
                list.insertAdjacentHTML('beforeend', tpl.replaceAll('__INDEX__', index));
            });
        </script>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan pengaturan</button>
        </div>
    </form>
@endsection