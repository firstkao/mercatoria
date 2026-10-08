{{--
    Wrapper field form (label + kontrol + hint + error).
    Pakai: <x-admin.field label="Judul" name="title"> <input ...> </x-admin.field>
    Biasanya tidak dipakai langsung — pakai x-admin.input / textarea / select.
--}}
@props([
    'label' => null,
    'name' => null,
    'hint' => null,
])

<label {{ $attributes->class(['field']) }}>
    @if ($label)<span>{{ $label }}</span>@endif
    {{ $slot }}
    @if ($hint)<p class="hint">{{ $hint }}</p>@endif
    @if ($name)@include('admin.partials.error', ['name' => $name])@endif
</label>
