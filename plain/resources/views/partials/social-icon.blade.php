{{-- Partial ikon sosial media.
     Dipakai: layouts/app.blade.php (header & footer), dikelola via Pengaturan Umum.
     Konsumsi variabel lokal via @include(['social' => $item]).

     BUG FIX (ikon tidak muncul walau data ada): pencocokan icon_key sebelumnya
     case-sensitive dan strict ('Instagram' / ' INSTAGRAM ' di database tidak
     pernah cocok dengan SVG bawaan). Kini key dinormalisasi (trim + lower)
     lebih dulu, dan icon_url yang hanya berisi spasi ikut dianggap kosong.

     BUG FIX (ikon Threads tampak gelap): Daripada mendeteksi gambar gelap via regex
     yang rawan lolos, sekarang kita prioritaskan SVG bawaan (currentColor) untuk
     platform yang sudah didukung. Gambar custom hanya dipakai untuk platform
     yang tidak punya SVG bawaan. --}}
@php
    $iconKey = \App\Support\PublicSocialMedia::normalizedIconKey($social);

    // Bila icon_key belum diisi admin, coba simpulkan dari nama (mis. baris
    // bernama "Threads" tanpa icon_key) supaya tetap memakai SVG bawaan dan
    // tidak jatuh ke gambar custom yang gelap.
    if ($iconKey === null) {
        $nameLower = strtolower(trim((string) ($social->name ?? '')));
        foreach (['threads', 'instagram', 'facebook', 'whatsapp'] as $candidate) {
            if ($nameLower !== '' && str_contains($nameLower, $candidate)) {
                $iconKey = $candidate;
                break;
            }
        }
    }

    $iconUrl = trim((string) ($social->icon_url ?? ''));

    // Daftar platform yang punya SVG bawaan di file ini.
    // Jika platform ada di sini, kita SELALU pakai SVG bawaan agar warnanya
    // otomatis menyesuaikan tema (terang/gelap) via currentColor.
    $hasBuiltInSvg = in_array($iconKey, ['instagram','facebook','x','threads','whatsapp'], true);
@endphp

@if ($iconUrl !== '' && ! $hasBuiltInSvg)
    {{-- Pakai gambar custom HANYA jika platform tidak punya SVG bawaan --}}
    <img src="{{ $iconUrl }}" alt="{{ $social->name }}" width="20" height="20" style="width:20px;height:20px;vertical-align:middle;">
@elseif ($iconKey === 'instagram')
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
    </svg>
@elseif ($iconKey === 'facebook')
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/>
    </svg>
@elseif ($iconKey === 'x')
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
    </svg>
@elseif ($iconKey === 'threads')
    {{-- Logo Threads resmi (Simple Icons, path solid) dengan fill currentColor
         agar warnanya identik dengan Facebook/X/WhatsApp di footer. --}}
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M18.263 11.097c-.03-3.486-1.92-5.586-5.111-5.586-2.13 0-3.922.963-4.863 2.499l2.062 1.438c.535-.843 1.272-1.543 2.628-1.543 1.528 0 2.318.85 2.544 2.431a15 15 0 0 0-2.236-.173c-4.125 0-6.068 1.867-6.068 4.336s1.943 3.99 4.804 3.99c3.139 0 5.013-2.115 5.781-4.735.798.361 1.348 1.204 1.348 2.47 0 3.387-3.907 5.232-7.22 5.232-4.885 0-8.077-3.207-8.077-8.424 0-6.392 4.223-10.487 9.9-10.487 3.808 0 5.69 1.671 6.97 3.914l2.108-1.475C21.44 2.078 18.331 0 13.663 0 6.227 0 1.168 5.277 1.168 12.934c0 7 4.953 11.066 10.856 11.066 4.878 0 9.809-2.846 9.809-7.716 0-2.545-1.46-4.231-3.569-5.187m-6.33 4.855c-1.077 0-2.026-.512-2.026-1.453 0-1.483 1.822-1.934 3.606-1.934.678 0 1.34.045 1.927.173-.422 1.927-1.671 3.215-3.508 3.214Z"/>
    </svg>
@elseif ($iconKey === 'whatsapp')
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
@else
    {{-- Fallback custom: teks nama pendek di lingkaran --}}
    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:currentColor;color:#fff;font-size:11px;font-weight:800;">{{ mb_strtoupper(mb_substr($social->name, 0, 1)) }}</span>
@endif
