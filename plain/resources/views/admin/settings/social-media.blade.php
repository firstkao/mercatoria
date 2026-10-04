@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@php($iconOptions = \App\Models\SocialMedia::BUILT_IN_ICONS)

@section('content')
    <div class="panel stack">
        <h2>Sosial media</h2>
        <p class="hint">Kelola ikon sosial media yang tampil di header & footer. Tambah/hapus bebas — semuanya dinamis dari database.</p>

        <table class="table">
            <thead>
                <tr>
                    <th style="width:48px;">Ikon</th>
                    <th>Nama</th>
                    <th>URL</th>
                    <th style="width:70px;">Urutan</th>
                    <th style="width:70px;">Status</th>
                    <th style="width:150px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($socialMedias as $sm)
                    <tr>
                        <td><span class="btn btn--ghost" style="pointer-events:none;">@include('partials.social-icon', ['social' => $sm])</span></td>
                        <td><strong>{{ $sm->name }}</strong>@if ($sm->icon_url)<br><small class="hint">Icon custom URL</small>@endif</td>
                        <td><small>{{ $sm->url ?: '—' }}</small></td>
                        <td>{{ $sm->sort_order }}</td>
                        <td>@if ($sm->is_active)<span class="badge badge--ok">Aktif</span>@else<span class="badge">Nonaktif</span>@endif</td>
                        <td style="text-align:right;">
                            <button type="button" class="btn btn--small" data-edit='@json($sm)'>Edit</button>
                            <form method="POST" action="{{ route('admin.settings.social-media.destroy', $sm) }}" style="display:inline;" onsubmit="return confirm('Hapus {{ $sm->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--small btn--danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="hint">Belum ada sosial media. Tambahkan lewat form di bawah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel stack" id="social-form-panel">
        <h3 id="social-form-title">Tambah Sosial Media</h3>
        <form method="POST" action="{{ route('admin.settings.social-media.store') }}" id="social-form" novalidate>
            @csrf
            <div class="grid-2">
                <label class="field">
                    <span>Nama *</span>
                    <input type="text" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Instagram / Threads / YouTube" required>
                    @include('admin.partials.error', ['name' => 'name'])
                </label>
                <label class="field">
                    <span>URL profil</span>
                    <input type="url" name="url" value="{{ old('url') }}" maxlength="2048" placeholder="https://instagram.com/mercatoria_id">
                    @include('admin.partials.error', ['name' => 'url'])
                </label>
                <label class="field">
                    <span>Ikon bawaan</span>
                    <select name="icon_key">
                        <option value="">— pilih —</option>
                        @foreach ($iconOptions as $ik)
                            <option value="{{ $ik }}">{{ ucfirst($ik) }}</option>
                        @endforeach
                    </select>
                    <small class="hint">Punya Instagram, Facebook, X, Threads, WhatsApp, TikTok.</small>
                </label>
                <label class="field">
                    <span>Icon URL (custom)</span>
                    <input type="url" name="icon_url" value="{{ old('icon_url') }}" maxlength="2048" placeholder="https://.../icon.png (PNG/SVG)">
                    <small class="hint">Dipakai kalau ikon bawaan tidak tersedia. Mengoverride ikon bawaan.</small>
                    @include('admin.partials.error', ['name' => 'icon_url'])
                </label>
                <label class="field">
                    <span>Urutan tampil</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" step="1">
                    @include('admin.partials.error', ['name' => 'sort_order'])
                </label>
                <label class="field">
                    <span>&nbsp;</span>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span>Aktif</span>
                    </label>
                </label>
            </div>
            <div class="savebar">
                <button type="submit" class="btn btn--primary" id="social-form-submit">Simpan</button>
                <button type="button" class="btn" id="social-form-cancel" hidden>Batal edit</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('social-form');
            const title = document.getElementById('social-form-title');
            const submitBtn = document.getElementById('social-form-submit');
            const cancelBtn = document.getElementById('social-form-cancel');
            let currentMethod = null;

            function fill(data) {
                form.name.value = data.name || '';
                form.url.value = data.url || '';
                form.icon_url.value = data.icon_url || '';
                form.icon_key.value = data.icon_key || '';
                form.sort_order.value = data.sort_order ?? 0;
                form.is_active.checked = !!data.is_active;
                title.textContent = 'Edit: ' + data.name;
                submitBtn.textContent = 'Update';
                cancelBtn.hidden = false;
                if (currentMethod) { currentMethod.remove(); }
                currentMethod = document.createElement('input');
                currentMethod.type = 'hidden';
                currentMethod.name = '_method';
                currentMethod.value = 'PUT';
                form.appendChild(currentMethod);
                form.action = '/admin/settings/social-media/' + data.id;
                document.getElementById('social-form-panel').scrollIntoView({ behavior: 'smooth' });
            }

            document.querySelectorAll('[data-edit]').forEach((btn) => {
                btn.addEventListener('click', () => fill(JSON.parse(btn.getAttribute('data-edit'))));
            });

            cancelBtn.addEventListener('click', () => {
                if (currentMethod) { currentMethod.remove(); currentMethod = null; }
                form.reset();
                form.action = '{{ route('admin.settings.social-media.store') }}';
                title.textContent = 'Tambah Sosial Media';
                submitBtn.textContent = 'Simpan';
                cancelBtn.hidden = true;
            });
        })();
    </script>
@endsection
