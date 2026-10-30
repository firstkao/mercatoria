@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Settings</h1>

    @php
        // Remap group DB ke sub-bagian tampilan; exclude marketing (promo bar hardcoded di blade).
        $sectionMap = [
            'display' => 'Umum', 'order' => 'Umum', 'product' => 'Umum',
            'social' => 'Umum', 'seo' => 'Umum', 'snapshot' => 'Umum',
            'security' => 'Keamanan',
            'coin' => 'Koin & Referral',
            'pricing' => 'Ongkir & Berat',
            'tier' => 'Tier & Denda', 'penalty' => 'Tier & Denda',
            'notification' => 'Notifikasi & Email', 'email' => 'Notifikasi & Email',
            'user' => 'Lainnya', 'splitbox' => 'Lainnya',
        ];
        $sections = [];
        foreach ($groups as $group => $items) {
            if ($group === 'marketing') {
                continue;
            }
            foreach ($items as $setting) {
                if ($group === 'coin' && str_starts_with($setting->key, 'voucher_birthday')) {
                    $sections['Voucher'][] = $setting;
                } else {
                    $sections[$sectionMap[$group] ?? 'Lainnya'][] = $setting;
                }
            }
        }

        $unitFor = function (string $key): string {
            if (str_contains($key, 'percent') || str_contains($key, 'opacity')) return '%';
            if (str_contains($key, 'chars')) return 'karakter';
            if (str_contains($key, 'days')) return 'hari';
            if (str_contains($key, 'hours')) return 'jam';
            if (str_contains($key, 'minutes') || str_contains($key, 'per_minute')) return 'menit';
            if (str_contains($key, 'seconds') || str_contains($key, 'polling')) return 'detik';
            if (str_contains($key, 'per_kg')) return 'Rp/kg';
            if (str_contains($key, 'weight')) return 'gram';
            if (str_starts_with($key, 'coin_')) return 'koin';
            if (preg_match('/(nominal|min_belanja|threshold|bonus|penalty|price|fee|rate|rounding|discount|purchase|ongkir)/', $key)) return 'Rp';
            return '';
        };

        $unitHints = ['(persen)', '(idr)', '(rp)', '(hari)', '(jam)', '(menit)', '(detik)', '(gram)', '(kg)', '(koin)', '(karakter)', '(%)'];
    @endphp

    {{-- Sub-bagian terorganisir (tabs) --}}
    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule" role="tablist">
        @foreach (array_keys($sections) as $i => $name)
            <button type="button" data-settings-tab="{{ $name }}"
                    class="settings-tab border-b-2 px-4 py-2 text-sm {{ $i === 0 ? 'border-ink font-semibold text-ink' : 'border-transparent text-ink/55 hover:text-ink' }}">
                {{ $name }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('office.settings.update') }}" class="mt-4">
        @csrf

        @foreach ($sections as $name => $items)
            <div class="settings-panel rounded-xl border border-rule bg-white p-4" data-settings-panel="{{ $name }}" {{ $loop->first ? '' : 'style="display:none"' }}>
                <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">{{ $name }}</h2>

                <div class="mt-3 space-y-3">
                    @foreach ($items as $setting)
                        @php
                            $unit = $unitFor($setting->key);
                            $label = trim($setting->description ?: str_replace('_', ' ', ucwords($setting->key, '_')), '. ');
                            $hasHint = $unit !== '' && collect($unitHints)->contains(fn ($h) => str_contains(strtolower($label), $h));
                        @endphp
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                            <label class="text-sm text-ink/80" for="setting-{{ $setting->key }}">
                                {{ $label }}{{ $unit !== '' && ! $hasHint ? ' (' . $unit . ')' : '' }}
                            </label>
                            <div class="md:col-span-2">
                                @if ($setting->type === 'bool')
                                    <select name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}" class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <option value="1" @selected($setting->value === '1')>Aktif</option>
                                        <option value="0" @selected($setting->value === '0')>Nonaktif</option>
                                    </select>
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                           value="{{ $setting->value }}"
                                           class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                @endif
                                @error('settings.'.$setting->key)<p class="text-xs text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="mt-4 rounded-lg bg-black px-6 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN SETTINGS</button>
    </form>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.settings-tab');
            var panels = document.querySelectorAll('[data-settings-panel]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                        t.classList.add('border-transparent', 'text-ink/55');
                    });
                    tab.classList.remove('border-transparent', 'text-ink/55');
                    tab.classList.add('border-ink', 'font-semibold', 'text-ink');
                    var key = tab.getAttribute('data-settings-tab');
                    panels.forEach(function (p) {
                        p.style.display = p.getAttribute('data-settings-panel') === key ? '' : 'none';
                    });
                });
            });
        })();
    </script>
@endsection
