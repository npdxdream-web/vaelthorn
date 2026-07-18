@php $vh ??= 140; @endphp
{{-- TL --}}
<g stroke="{{ $c }}" fill="none">
    <line x1="6" y1="6" x2="50" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="50" stroke-width="0.9"/>
    <line x1="12" y1="6" x2="12" y2="40" stroke-width="0.45"/>
    <line x1="6" y1="12" x2="40" y2="12" stroke-width="0.45"/>
    <line x1="18" y1="6" x2="18" y2="26" stroke-width="0.25"/>
    <line x1="6" y1="18" x2="26" y2="18" stroke-width="0.25"/>
    <line x1="6" y1="6" x2="22" y2="6" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="15" y2="15" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="6" y2="22" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="11" y2="20" stroke-width="0.4"/>
    <line x1="6" y1="6" x2="20" y2="11" stroke-width="0.4"/>
</g>
{{-- TR --}}
<g stroke="{{ $c }}" fill="none" transform="translate(140,0) scale(-1,1)">
    <line x1="6" y1="6" x2="50" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="50" stroke-width="0.9"/>
    <line x1="12" y1="6" x2="12" y2="40" stroke-width="0.45"/>
    <line x1="6" y1="12" x2="40" y2="12" stroke-width="0.45"/>
    <line x1="6" y1="6" x2="22" y2="6" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="15" y2="15" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="6" y2="22" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="11" y2="20" stroke-width="0.4"/>
    <line x1="6" y1="6" x2="20" y2="11" stroke-width="0.4"/>
</g>
{{-- BL --}}
<g stroke="{{ $c }}" fill="none" transform="translate(0,{{ $vh }}) scale(1,-1)">
    <line x1="6" y1="6" x2="50" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="50" stroke-width="0.9"/>
    <line x1="12" y1="6" x2="12" y2="40" stroke-width="0.45"/>
    <line x1="6" y1="12" x2="40" y2="12" stroke-width="0.45"/>
    <line x1="6" y1="6" x2="22" y2="6" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="15" y2="15" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="6" y2="22" stroke-width="0.7"/>
</g>
{{-- BR --}}
<g stroke="{{ $c }}" fill="none" transform="translate(140,{{ $vh }}) scale(-1,-1)">
    <line x1="6" y1="6" x2="50" y2="6" stroke-width="0.9"/>
    <line x1="6" y1="6" x2="6" y2="50" stroke-width="0.9"/>
    <line x1="12" y1="6" x2="12" y2="40" stroke-width="0.45"/>
    <line x1="6" y1="12" x2="40" y2="12" stroke-width="0.45"/>
    <line x1="6" y1="6" x2="22" y2="6" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="15" y2="15" stroke-width="0.7"/>
    <line x1="6" y1="6" x2="6" y2="22" stroke-width="0.7"/>
</g>
