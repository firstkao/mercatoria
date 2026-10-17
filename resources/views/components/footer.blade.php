<footer class="mt-12 border-t border-border bg-surface">
    <div class="mx-auto max-w-7xl px-4 py-8 text-center text-sm text-text-muted">
        <p>&copy; {{ date('Y') }} Mercatoria — Marketplace Merch IP Cina</p>
        <p class="mt-1 text-xs">
            <a href="mailto:{{ config('mercatoria.store.cs_email', 'cs@mercatoria.id') }}" class="hover:text-text">
                {{ config('mercatoria.store.cs_email', 'cs@mercatoria.id') }}
            </a>
        </p>
    </div>
</footer>