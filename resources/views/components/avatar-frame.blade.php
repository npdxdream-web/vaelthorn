@props([
    'rank'   => 'stranger',   // auto_rank key or user role key
    'frame'  => null,         // explicit override ('fan','step','simple') — skips rank lookup
    'size'   => 140,          // width in px
    'height' => null,         // height in px; null = square (same as size)
    'initial'=> '?',
    'color'  => null,
])

@php
// Vaelthorn rank/role → frame mapping
$frameConfig = [
    'legend'     => ['color' => '#c8a84b', 'style' => 'fan'],
    'veteran'    => ['color' => '#9b8fc8', 'style' => 'step'],
    'traveler'   => ['color' => '#c87c3a', 'style' => 'step'],
    'wanderer'   => ['color' => '#8ab0c8', 'style' => 'simple'],
    'stranger'   => ['color' => '#746a5a', 'style' => 'simple'],
    'superadmin' => ['color' => '#c8a84b', 'style' => 'fan'],
    'admin'      => ['color' => '#c8a84b', 'style' => 'fan'],
    'moderator'  => ['color' => '#6890c8', 'style' => 'step'],
];

$key    = strtolower($rank);
$cfg    = $frameConfig[$key] ?? $frameConfig['stranger'];
$c      = $color ?? $cfg['color'];
$style  = $frame ?? $cfg['style'];   // explicit frame prop overrides rank lookup

// SVG dimensions: fixed 140-unit wide viewBox, height scales with aspect ratio
$h   = $height ?? $size;
$vw  = 140;
$vh  = (int) round(140 * $h / $size);
$mx  = 70;   // mid-x in viewBox (always 70)
@endphp

<div style="position:relative;width:{{ $size }}px;height:{{ $h }}px;flex-shrink:0;
            background:linear-gradient(175deg,#1c1915 0%,#0d0c0a 62%,#090807 100%);
            box-shadow:0 0 20px {{ $c }}18,inset 0 0 0 1px {{ $c }}12,inset 0 0 28px rgba(0,0,0,0.6);">
    {{-- Avatar / initial --}}
    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            <span style="font-size:{{ round($size * 0.32) }}px;color:{{ $c }};
                         font-family:var(--font-decorative);
                         font-weight:700;line-height:1;
                         text-shadow:0 0 24px {{ $c }}88;">
                {{ strtoupper(mb_substr($initial, 0, 1)) }}
            </span>
        @endif
    </div>

    {{-- SVG Art Deco frame overlay --}}
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 {{ $vw }} {{ $vh }}"
         style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;">

        {{-- Outer border --}}
        <rect x="2" y="2" width="{{ $vw - 4 }}" height="{{ $vh - 4 }}"
              fill="none" stroke="{{ $c }}" stroke-width="0.4"/>

        {{-- Corner ornaments (pass $vh so BL/BR land at actual bottom corners) --}}
        @if($style === 'fan')
            @include('components.frames._fan-corner', ['c' => $c, 'vh' => $vh])
        @elseif($style === 'step')
            @include('components.frames._step-corner', ['c' => $c, 'vh' => $vh])
        @else
            @include('components.frames._simple-corner', ['c' => $c, 'vh' => $vh])
        @endif

        {{-- Top center diamond + horizontal lines --}}
        <polygon points="{{ $mx }},3 {{ $mx+3 }},7 {{ $mx }},11 {{ $mx-3 }},7" fill="{{ $c }}"/>
        <line x1="20" y1="7" x2="57" y2="7" stroke="{{ $c }}" stroke-width="0.3"/>
        <line x1="83" y1="7" x2="120" y2="7" stroke="{{ $c }}" stroke-width="0.3"/>

        {{-- Bottom center diamond + horizontal lines --}}
        <polygon points="{{ $mx }},{{ $vh-11 }} {{ $mx+3 }},{{ $vh-7 }} {{ $mx }},{{ $vh-3 }} {{ $mx-3 }},{{ $vh-7 }}" fill="{{ $c }}"/>
        <line x1="20" y1="{{ $vh-7 }}" x2="57" y2="{{ $vh-7 }}" stroke="{{ $c }}" stroke-width="0.3"/>
        <line x1="83" y1="{{ $vh-7 }}" x2="120" y2="{{ $vh-7 }}" stroke="{{ $c }}" stroke-width="0.3"/>
    </svg>
</div>
