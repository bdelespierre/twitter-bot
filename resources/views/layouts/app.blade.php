<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @section('head.meta')
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @show

    <title>{{ config('app.name', 'Twitter Bot') }}</title>

    @section('head.styles')
        <link href="{{ asset('css/app.css', Request::secure()) }}" rel="stylesheet">
    @show
</head>
<body>
    <div id="app" class="container">
        @yield('content')
    </div>

    @section('body.scripts')
        <script src="{{ asset('js/app.js', Request::secure()) }}"></script>
    @show
</body>
</html>
