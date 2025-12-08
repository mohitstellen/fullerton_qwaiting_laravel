<!DOCTYPE html>
<html lang="{{ session('app_locale', app()->getLocale()) }}" dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Appointment Booking Module' }}</title>
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
    
    <script src="{{ asset('js/cdn/tailwind3.js') }}"></script>
    <script src="{{ asset('js/cdn/tailwind4.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css?v='.time()) }}">
    
    @if(session('app_locale') === 'ar')
        <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
    @endif
    
    @livewireStyles
    @stack('styles')
</head>
<body>
    {{ $slot }}
    
    @livewireScripts
    <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
    @stack('scripts')
</body>
</html>
