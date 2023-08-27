<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - {{ Config::get('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ URL::asset("/bower_components/admin-lte/bootstrap/css/bootstrap.min.css")}}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{"https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"}}">
    <!-- Ionicons -->
    <!-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> -->
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ URL::asset( "/bower_components/admin-lte/dist/css/AdminLTE.min.css") }}">
    <link rel="stylesheet" href="{{ URL::asset("/bower_components/admin-lte/dist/css/skins/skin-black.min.css" )}}">    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="wrapper">           
        @yield('content')        
        <!-- Main Footer -->
        <footer class="footer" style="padding: 0px 10px">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
            Tecnología en Movimiento
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; 2021 <a href="#">Eglobalt</a></strong>.
    </footer>
    </div>  <!-- ./wrapper -->  
    <!-- jQuery 2.1.4 -->
    {{--<script src="{{ public_path('/js/jquery.js') }}"></script>--}}
    <script src="{{ "/bower_components/admin-lte/plugins/jQuery/jQuery-2.1.4.min.js" }}"></script>
    <script src="/js/jquery-ui.js"></script>

    {{--{!! Html::script('assets/js/libs/libs.js') !!}--}}
    <!-- REQUIRED JS SCRIPTS -->
    <!-- Bootstrap 3.3.4 -->
    <script src="{{ "/bower_components/admin-lte/bootstrap/js/bootstrap.min.js" }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ "/bower_components/admin-lte/dist/js/app.min.js" }}"></script>
    <script src="{{ "/assets/js/libs/libs.js" }}"></script>
    @yield('js')
</body>
</html>