<!DOCTYPE html>
{{-- <html lang="en" data-theme="light"> --}}


<html lang="{{ session('app_locale', app()->getLocale()) }}"
      dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Qwaiting'}}</title>

    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
    <script src="{{ asset('js/cdn/tailwind3.js') }}"></script>
    <script src="{{ asset('js/cdn/tailwind4.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

       @if(session('app_locale') === 'ar')
        <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/queue.css') }}">

    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>


    <link rel="stylesheet" href="{{ asset('css/cdn/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cdn/main.min.css') }}">


    @stack('styles')
    @livewireStyles
    <style>
        .header_lang {
            position: absolute;
            top: 0;
            left: 100%;
            transform: translate(-100%, 0);
            width: 200px;
            background-color: #f0f0f0;
        }

        .header_lang select {
            padding: 5px;
        }

        .header_sec {
            display: flex;
            position: absolute;
            align-items: baseline;
            right: 10px;
            top: 10px
        }
        .hidden {
            display: none !important;
        }
        .main-outer{
            min-height:100vh;
            padding: 25px;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }
        .main{
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: white;
            width: 100%;
            max-width:800px;
            margin:auto;
            border-radius:18px;
            padding: 18px;
        }
        </style>

</head>

<body>
<div>
   <div>
    @livewire('header')
     <livewire:package.subscription-reminder />
          <livewire:package.subscription-warning wire:poll.86400s />
    {{ $slot }}
    </div>
    </div>
    @livewireScripts
    <script src="{{ asset('js/cdn/flowbite.min.js') }}"></script>
    <script src="{{ asset('js/cdn/flatpickr.js') }}"></script>
    <script src="{{ asset('js/cdn/pusher.min.js') }}"></script>
    <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
    <script src="{{asset('/js/fullscreen.min.js?v=3.1.0.0')}}"></script>

    <script type="module" src="{{ asset('/js/app/echo-unauth.js?v='.time()) }}" type="module"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
   <script src="{{asset('/js/app/desktop-notification.js?v='.time())}}"></script>
   @stack('scripts')
</body>

</html>
