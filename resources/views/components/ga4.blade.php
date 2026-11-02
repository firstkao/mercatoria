@php($ga4Id = trim((string) setting('ga4_measurement_id', '')))
@if (preg_match('/^G-[A-Z0-9]{4,20}$/', $ga4Id))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($ga4Id));
    </script>
@endif
