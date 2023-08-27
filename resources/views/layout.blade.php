<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title') - {{ Config::get('app.name') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @yield('refresh')
    <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' }}">
    <!-- Ionicons -->
    <!-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> -->
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/dist/css/skins/skin-black.min.css') }}">
    @include('partials._css')
    @yield('aditional_css')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
    
    <livewire:styles />
</head>

<body class="skin-black sidebar-mini">
    <div class="wrapper">
        @include('partials._header')
        <!-- Left side column. contains the logo and sidebar -->
        @if (!Sentinel::getUser()->hasRole('red_claro'))
            @include('partials._sidebar')
        @else
            @include('partials._sidebar_claro')
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
            <a id="back-to-top" href="#" class="btn bg-orange btn-lg back-to-top" role="button"
                title="Volver arriba" data-toggle="tooltip" data-placement="left"><span
                    class="glyphicon glyphicon-chevron-up"></span></a>
        </div><!-- /.content-wrapper -->
        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->
            <div class="pull-right hidden-xs">
                Tecnología en Movimiento
            </div>
            <!-- Default to the left -->
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Eglobalt</a></strong>.
        </footer>
        <!-- Control Sidebar -->
        {{-- @include('partials._control_sidebar') --}}
        <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
    @include('partials._js')
    @yield('js')
    <livewire:scripts />
</body>

</html>
