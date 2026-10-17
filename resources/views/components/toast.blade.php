@php
    $error = session('error');
    $msg = session('success') ?? session('status');
    // Pesan kirim ulang verifikasi sudah ditangani banner, jangan dobel
    $msg = $msg === 'verification-link-sent' ? null : $msg;
@endphp

@if ($msg || $error)
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)"
         x-show="show" x-transition
         class="fixed right-4 top-20 z-50 max-w-sm rounded-lg px-4 py-3 text-sm text-white shadow-lg {{ $error ? 'bg-danger-500' : 'bg-success-500' }}"
         role="alert" aria-live="assertive">
        {{ $error ?? $msg }}
    </div>
@endif