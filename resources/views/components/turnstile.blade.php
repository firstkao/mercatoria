@php($turnstileSiteKey = (string) config('services.turnstile.site_key'))
@if ($turnstileSiteKey !== '' && (string) config('services.turnstile.secret') !== '')
    <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
    @error('cf-turnstile-response')
        <p class="text-xs text-danger">{{ $message }}</p>
    @enderror
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
