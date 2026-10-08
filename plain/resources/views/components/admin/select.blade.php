{{--
    Select (label + error otomatis).
    Pakai:
      <x-admin.select name="status" label="Status" :options="[''=>'Semua','a'=>'Aktif']" :selected="$status" />
      atau isi lewat slot: <x-admin.select name="game_id" label="Game"><option ...>...</option></x-admin.select>
--}}
@props([
    'name',
    'label' => null,
    'hint' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

<x-admin.field :label="$label" :name="$name" :hint="$hint">
    <select name="{{ $name }}" {{ $attributes }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $selected) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
</x-admin.field>
