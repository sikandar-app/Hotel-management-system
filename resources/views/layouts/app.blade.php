<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <title>{{ config('app.name') }}</title>
    <meta content="HotelPro is a powerful booking management admin panel built with Vue.js and Bootstrap 5, designed to manage rooms, guests, and payments seamlessly." name="description" />
    <link rel="stylesheet" href="{{ Vite::asset('resources/sass/app.scss') }}" id="layout-css">
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script>

    @yield('css')
</head>

<body data-sidebar="dark" data-layout-mode="light">
    <div id="app">
        @yield('content')
    </div>
    <!-- built files will be auto injected -->
    @stack('scripts')
    @yield('js')
    
</body>
</html>
