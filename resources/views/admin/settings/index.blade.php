@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('content')
    <h1 class="text-xl font-semibold">Settings</h1>

    <form method="POST" action="{{ route('office.settings.update') }}" class="mt-4 space-y-6">
        @csrf

        @foreach ($groups as $group => $items)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold">{{ ucfirst($group) }}</h2>

                <div class="mt-3 space-y-3">
                    @foreach ($items as $setting)
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                            <label class="text-sm" for="setting-{{ $setting->key }}">
                                {{ $setting->key }}
                                <span class="block text-xs text-gray-400">{{ $setting->description }}</span>
                            </label>
                            <div class="md:col-span-2">
                                @if ($setting->type === 'bool')
                                    <select name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <option value="1" @selected($setting->value === '1')>Aktif</option>
                                        <option value="0" @selected($setting->value === '0')>Nonaktif</option>
                                    </select>
                                @else
                                    <input type="text" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}"
                                           value="{{ $setting->value }}"
                                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @endif
                                @error('settings.'.$setting->key)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white">SIMPAN SETTINGS</button>
    </form>
@endsection
