<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title') - {{ Config::get('app.name') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @yield('refresh')
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' }}">
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/dist/css/skins/skin-black.min.css') }}">
    @include('partials._css')
    @yield('aditional_css')
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
</head>

<body class="skin-black sidebar-mini">
    <div class="wrapper">
        @include('terminal_interaction._header')
        @include('terminal_interaction._sidebar')
        <div class="content-wrapper">
            @yield('content')
            <a id="back-to-top" href="#" class="btn bg-orange btn-lg back-to-top" role="button" title="Volver arriba"
                data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-up"></span></a>
        </div>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                Tecnología en Movimiento
            </div>
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Eglobalt</a></strong>.
        </footer>
        <div class="control-sidebar-bg"></div>
    </div>
    @include('terminal_interaction._js')
    @yield('js')
</body>

</html>
