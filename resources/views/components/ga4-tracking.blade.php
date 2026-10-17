@php
    $measurementId = (string) setting('ga4_measurement_id', '');
    $enabled = preg_match('/\AG-[A-Z0-9]{4,20}\z/', $measurementId) === 1;
    $events = session('ga4_events', []);
    $events = is_array($events) ? $events : [];
    if (session()->has('ga4_event')) {
        $events[] = session('ga4_event');
    }
@endphp

@if ($enabled)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', @js($measurementId));
        window.mercatoriaTrack = function (name, parameters = {}) {
            window.gtag('event', name, parameters);
        };
        @foreach ($events as $event)
            @if (is_array($event) && isset($event['name']))
                window.mercatoriaTrack(@js($event['name']), @js($event['params'] ?? []));
            @endif
        @endforeach
    </script>
@endif
