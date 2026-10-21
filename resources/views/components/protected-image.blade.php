@props(['src', 'alt' => ''])

<div class="protected-image-wrap">
    <img src="{{ $src }}" alt="{{ $alt }}" draggable="false" {{ $attributes->class('protected-image') }}>
    <div class="img-shield-overlay" aria-hidden="true"></div>
</div>
