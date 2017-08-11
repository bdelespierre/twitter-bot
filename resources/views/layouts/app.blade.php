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

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    @show
</head>
<body class="admin-panel theme-dark">
    <!-- Navigation -->
    <nav class="navbar fixed-top navbar-toggleable-md mr-4">

        <!-- Brand and toggle get grouped for better mobile display -->
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="#"><i class="fa fa-twitter-square text-info align-bottom" style="font-size: 150%"></i> {{ config('app.name', 'Twitter Bot') }}</a>

        <!-- Top Menu Items -->
        <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
            <ul class="navbar-nav ml-auto mt-2 mt-md-0">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">Disabled</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="http://example.com" id="navbar-dropdown-theme" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-fw fa-cog"></i> Theme
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbar-dropdown-theme">
                        <a class="dropdown-item" href="#" data-theme="light">Light</a>
                        <a class="dropdown-item" href="#" data-theme="dark">Dark</a>
                    </div>
                </li>

            </ul>
        </div>
    </nav>

    <!-- Menu -->
    <ul class="nav flex-column pt-3">
        <li class="nav-item py-1">
            <a class="nav-link disabled" href="#">
                <i class="fa fa-fw fa-tachometer"></i> Dashboard
            </a>
        </li>
        <li class="nav-item py-1">
            <a class="nav-link" href="{{ route('users.index') }}">
                <i class="fa fa-fw fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item py-1">
            <a class="nav-link" href="{{ route('pool.index') }}">
                <i class="fa fa-fw fa-th-list"></i> Pool Items
            </a>
        </li>
        <li class="nav-item py-1">
            <a class="nav-link" href="{{ route('buffer.index') }}">
                <i class="fa fa-fw fa-list-ol"></i> Buffer Items
            </a>
        </li>
    </ul>

    <!-- Content -->
    <div id="container">
        <div id="app" class="container-fluid bg-white rounded-top mr-5 py-3">
            @yield('content')
        </div>
    </div>

    @section('body.scripts')
        <script src="{{ asset('js/app.js', Request::secure()) }}"></script>

        <script type="text/javascript">
            $(function() {
                $(document).on('click', '#container a:not([target="_blank"])', function(e) {
                    e.preventDefault();

                    $('#app>*').css('opacity', .3);
                    $('#container').load($(this).attr('href')+' #app');
                })

                $('[data-theme]').click(function(e) {
                    e.preventDefault();

                    $('body').removeClass('theme-light theme-dark').addClass('theme-' + theme);
                })
            })
        </script>
    @show
</body>
</html>
