@props(['name', 'maxWidth' => 'max-w-md'])

<div x-data="{ show: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show"
     x-cloak
     x-on:click.self="show = false"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">

    <div class="w-full {{ $maxWidth }} rounded-lg bg-white p-6"
         role="dialog"
         aria-modal="true"
         x-trap.noscroll="show">
        {{ $slot }}
    </div>
</div>