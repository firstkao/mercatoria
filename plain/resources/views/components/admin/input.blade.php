{{--
    Input teks (label + error otomatis).
    Pakai: <x-admin.input name="title" label="Judul" :value="old('title', $page->title)" required maxlength="255" />
--}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'hint' => null,
    'value' => null,
])

<x-admin.field :label="$label" :name="$name" :hint="$hint">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes }}>
</x-admin.field>
