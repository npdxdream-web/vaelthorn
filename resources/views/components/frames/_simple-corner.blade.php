@php $vh ??= 140; @endphp
{{-- TL --}}
<g stroke="{{ $c }}" fill="none">
    <line x1="6" y1="6" x2="40" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="40" stroke-width="0.9"/>
</g>
{{-- TR --}}
<g stroke="{{ $c }}" fill="none" transform="translate(140,0) scale(-1,1)">
    <line x1="6" y1="6" x2="40" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="40" stroke-width="0.9"/>
</g>
{{-- BL --}}
<g stroke="{{ $c }}" fill="none" transform="translate(0,{{ $vh }}) scale(1,-1)">
    <line x1="6" y1="6" x2="40" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="40" stroke-width="0.9"/>
</g>
{{-- BR --}}
<g stroke="{{ $c }}" fill="none" transform="translate(140,{{ $vh }}) scale(-1,-1)">
    <line x1="6" y1="6" x2="40" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="40" stroke-width="0.9"/>
</g>
