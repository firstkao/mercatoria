@php($waHref = 'https://wa.me/'.config('mercatoria.store.wa_admin'))
<div x-data="{ top: false }" x-on:scroll.window.passive="top = window.scrollY > 400"
     class="fixed bottom-4 right-4 z-40 flex flex-col items-end gap-3">
    <button type="button" x-show="top" x-cloak x-transition @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            aria-label="Kembali ke atas"
            class="flex h-11 w-11 items-center justify-center rounded-full bg-navy-500 text-lg font-bold text-white shadow-lg hover:bg-navy-400">^</button>
    <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp Admin" title="WhatsApp Admin"
       class="flex h-12 w-12 items-center justify-center rounded-full bg-success-500 text-2xl text-white shadow-lg hover:opacity-90">💬</a>
</div>
