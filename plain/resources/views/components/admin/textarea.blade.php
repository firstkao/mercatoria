{{--
    Textarea (label + error otomatis).
    Pakai: <x-admin.textarea name="description" label="Deskripsi" :value="old('description', $product->description)" rows="6" />
--}}
@props([
    'name',
    'label' => null,
    'hint' => null,
    'value' => null,
    'rows' => 4,
])

<x-admin.field :label="$label" :name="$name" :hint="$hint">
    <textarea name="{{ $name }}" rows="{{ $rows }}" {{ $attributes }}>{{ old($name, $value) }}</textarea>
</x-admin.field>
