<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="id"></div>
@include('partials.field-error', ['name' => 'cf-turnstile-response'])

@push('scripts')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endpush