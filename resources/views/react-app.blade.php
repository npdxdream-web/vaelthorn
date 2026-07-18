@extends('layouts.app')

@php
    $viteAssets = ['resources/frontend/vaelthorn-ui/main.tsx'];
@endphp

@section('title', 'Vaelthorn App')

@section('content')
<div id="root" class="min-h-screen bg-bg"></div>
@endsection
