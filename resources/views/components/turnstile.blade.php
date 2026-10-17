@if (app(\App\Services\Security\TurnstileService::class)->isConfigured())
    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
    @error('cf-turnstile-response')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
@elseif (app()->isProduction())
    <p role="alert" class="text-sm text-red-700">Verifikasi keamanan belum dikonfigurasi. Silakan hubungi administrator.</p>
@endif
