@props(['product', 'shipping', 'matchedVariant' => null, 'simple' => false])

@php
    $pricing = $shipping->calculateItemShipping($product, $matchedVariant);
    $tags = $product->tags;
    $href = '/'.$product->game->slug.'/'.$product->slug
        .($matchedVariant ? '?variant='.$matchedVariant->id : '');
@endphp

@once
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .pc-card, .pc-card *, .pc-card *::before, .pc-card *::after { box-sizing: border-box; }
        .pc-card {
            display: flex; flex-direction: column; height: 100%; overflow: hidden;
            background: #fff; border: 1px solid #e1e1e1; border-radius: 0;
            font-family: 'Roboto', system-ui, sans-serif; color: #333;
            text-decoration: none; -webkit-font-smoothing: antialiased;
            transition: border-color .15s;
        }
        .pc-card:hover { border-color: #000; }

        .pc-img { position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: #fff; }
        .pc-img > img {
            display: block; width: 100%; height: 100%; object-fit: cover;
            transition: transform .3s ease;
        }
        .pc-card:hover .pc-img > img { transform: scale(1.03); }
        .pc-noimg {
            display: flex; width: 100%; height: 100%; align-items: center; justify-content: center;
            font-size: 11px; line-height: 16px; letter-spacing: .15em; text-transform: uppercase; color: #aaa;
        }

        .pc-tags { position: absolute; top: 0; left: 0; z-index: 2; display: flex; flex-direction: column; align-items: flex-start; gap: 2px; }
        .pc-flag {
            height: 22px; padding: 0 16px 0 9px; line-height: 22px;
            font-size: 11px; font-weight: 600; letter-spacing: .02em; text-transform: uppercase; color: #fff;
            background: #FF4949;
            clip-path: polygon(0 0, 100% 0, calc(100% - 9px) 100%, 0 100%);
        }
        .pc-flag.is-presale { background: #3986C6; }
        .pc-flag.is-new { background: #4FC3F7; }
        .pc-flag.is-limited { background: #FF4949; }
        .pc-flag-more {
            height: 18px; padding: 0 8px; line-height: 18px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; color: #fff;
            background: rgba(0, 0, 0, .6);
        }

        .pc-body { display: flex; flex: 1; flex-direction: column; gap: 4px; padding: 12px; }
        .pc-meta { display: flex; align-items: center; gap: 6px; }
        .pc-game {
            margin: 0; font-size: 11px; line-height: 16px; font-weight: 500;
            letter-spacing: .12em; text-transform: uppercase; color: #888;
        }
        .pc-dev {
            display: inline-block; padding: 1px 6px; border: 1px solid #e1e1e1; background: #fff;
            font-size: 9px; line-height: 14px; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; color: #888;
        }

        .pc-name {
            margin: 0; font-size: 14px; line-height: 21px; font-weight: 500; color: #000;
        }

        .pc-variant {
            display: inline-block; width: fit-content; padding: 2px 8px;
            border: 1px solid #e1e1e1; background: #fff;
            font-size: 11px; line-height: 16px; color: #02a1ea;
        }

        .pc-price { margin-top: auto; padding-top: 6px; }
        .pc-was { font-size: 12px; line-height: 18px; color: rgba(0, 0, 0, .4); text-decoration: line-through; }
        .pc-now { font-size: 14px; line-height: 21px; font-weight: 500; color: #02a1ea; }
    </style>
@endonce

<a href="{{ $href }}" class="pc-card">
    <div class="pc-img">
        @if ($matchedVariant?->image_path)
            <img src="{{ Storage::url($matchedVariant->image_path) }}" alt="{{ $product->name }}">
        @elseif ($product->image_path)
            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}">
        @else
            <div class="pc-noimg">Tanpa Foto</div>
        @endif

        @if ($tags->isNotEmpty())
            <div class="pc-tags">
                @foreach ($tags->take(2) as $tag)
                    <span class="{{ $tag->slug === 'presale' ? 'pc-flag is-presale' : ($tag->slug === 'limited' ? 'pc-flag is-limited' : ($tag->slug === 'new' ? 'pc-flag is-new' : 'pc-flag')) }}">
                        {{ $tag->name }}
                    </span>
                @endforeach
                @if ($tags->count() > 2)
                    <span class="pc-flag-more">+{{ $tags->count() - 2 }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="pc-body">
        @unless ($simple)
            <div class="pc-meta">
                <p class="pc-game">[{{ $product->game->name }}]</p>
                @if ($product->developer)
                    <span class="pc-dev">{{ $product->developer->name }}</span>
                @endif
            </div>
        @endunless

        <p class="pc-name">{{ $product->name }}</p>

        @if ($matchedVariant)
            <p class="pc-variant">
                Varian tersedia: {{ $matchedVariant->name }}
            </p>
        @endif

        <div class="pc-price">
            @if ($product->hasActiveDiscount())
                <span class="pc-was">{{ format_rp($pricing['base_price_bersih']) }}</span>
            @endif
            <span class="pc-now">{{ format_rp($pricing['final_price_bersih']) }}</span>
        </div>
    </div>
</a>
