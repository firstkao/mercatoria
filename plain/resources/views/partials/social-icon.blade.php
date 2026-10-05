{{-- Partial ikon sosial media.
     Dipakai: layouts/app.blade.php (header & footer), dikelola via Pengaturan Umum.
     Konsumsi variabel lokal via @include(['social' => $item]).

     BUG FIX (ikon tidak muncul walau data ada): pencocokan icon_key sebelumnya
     case-sensitive dan strict ('Instagram' / ' INSTAGRAM ' di database tidak
     pernah cocok dengan SVG bawaan). Kini key dinormalisasi (trim + lower)
     lebih dulu, dan icon_url yang hanya berisi spasi ikut dianggap kosong. --}}
@php
    $iconKey = \App\Support\PublicSocialMedia::normalizedIconKey($social);
    $iconUrl = trim((string) ($social->icon_url ?? ''));
    // BUG FIX (ikon Threads tampak gelap): icon_url lama tersimpan sebagai
    // PNG icons8 dengan color=000000 (hitam pekat). Di footer berlatar gelap,
    // gambar hitam itu nyaris tak terlihat sementara sosmed lain memakai SVG
    // putih (currentColor). Solusi: bila URL custom berwarna gelap DAN ada SVG
    // bawaan untuk icon_key-nya, pakai SVG bawaan agar seragam.
    $isDarkCustomIcon = $iconUrl !== '' && preg_match(
        '/color=0{3,8}|color=black|&?color=%5B%22000/i',
        urldecode($iconUrl)
    );
    $hasBuiltInSvg = in_array($iconKey, ['instagram','facebook','x','threads','whatsapp','tiktok'], true);
@endphp
@if ($iconUrl !== '' && ! ($isDarkCustomIcon && $hasBuiltInSvg))
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
    {{-- FIX: path lama adalah glyph "t" Threads yang TIDAK simetris dan bolong-bolong,
         sehingga di footer (latar gelap) tampak lebih tipis/gelap dibanding ikon lain.
         Kini memakai logo resmi @threads (knot dua loop) dengan fill currentColor
         agar warnanya identik dengan Facebook/X/TikTok/WhatsApp. --}}
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M19.094 7.412c.412-3.417-1.382-5.415-4.179-5.859-2.2-0.352-4.564-0.013-6.334 1.019C6.678 3.795 5.593 5.732 5.99 8.37c.383 2.553 2.348 4.257 4.87 4.622.144.02.288.037.433.051-.138.822-.018 1.567.357 1.943.495.495 1.29.41 1.916-.193.525-.506.896-1.196 1.12-1.894 1.046.224 1.988.16 2.589.104.856-.078 1.558-.432 1.984-1.008.285-.386.456-.879.517-1.477.292-.086.53-.2.727-.342.444-.322.72-.81.81-1.41.106-.712-.243-1.317-.786-1.557a.85.85 0 0 0-.233-.07zM13.69 12.76c-.13.52-.35 1.045-.66 1.446-.24.3-.53.477-.79.477-.12 0-.2-.04-.25-.09-.14-.14-.18-.56-.05-1.2.55.07 1.1.1 1.63.07l.12-.7z m1.73-3.37c-.17.06-.36.1-.56.12-.55.08-1.16.07-1.79-.01-.16-.02-.32-.04-.48-.07-1.76-.31-3.01-1.56-2.81-3.1.16-1.22 1.27-2.06 2.97-2.06.31 0 .63.03.94.08 1.72.28 2.89 1.45 2.71 2.83-.05.44-.23.83-.52 1.15.19-.06.37-.1.52-.1.29-.02.55.05.74.19.24.18.36.45.32.77-.05.38-.32.66-.74.83z m-2.42-1.6c-.22-.04-.45-.06-.68-.06-.9 0-1.6.4-1.73 1.03-.08.42.22.83.79.95.23.05.47.07.71.07.5 0 .96-.13 1.31-.36.43-.3.66-.73.6-1.14-.06-.4-.42-.7-.99-.84z"/>
    </svg>
@elseif ($iconKey === 'whatsapp')
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
@else
    {{-- Fallback custom: teks nama pendek di lingkaran --}}
    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:currentColor;color:#fff;font-size:11px;font-weight:800;">{{ mb_strtoupper(mb_substr($social->name, 0, 1)) }}</span>
@endif
