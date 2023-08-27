<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Eglobalt - Acceso de Usuarios</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ "/bower_components/admin-lte/bootstrap/css/bootstrap.min.css" }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ "/bower_components/admin-lte/dist/css/AdminLTE.min.css" }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ "/bower_components/admin-lte/plugins/iCheck/square/blue.css" }}">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>Eglobalt</b>T</a>
    </div><!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Ingrese sus credenciales</p>
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Problemas</strong> al iniciar sesión
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}" accept-charset="UTF-8">

            <div class="form-group  has-feedback {{ ($errors->has('username')) ? 'has-error' : '' }}">
                <input class="form-control" placeholder="Nombre de Usuario" autofocus="autofocus" name="username" type="text" value="{{ Request::old('username') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                {{ ($errors->has('username') ? $errors->first('username') : '') }}
            </div>
            <div class="form-group has-feedback {{ ($errors->has('password')) ? 'has-error' : '' }}">
                <input class="form-control" placeholder="Password" name="password" value="" type="password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                {{ ($errors->has('password') ?  $errors->first('password') : '') }}

            </div>
            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input name="rememberMe" value="rememberMe" type="checkbox"> Recordarme
                        </label>
                    </div>
                </div><!-- /.col -->
                <div class="col-xs-4">
                    <input name="_token" value="{{ csrf_token() }}" type="hidden">
                    <input class="btn btn-primary" value="Acceder" type="submit">
                </div><!-- /.col -->
            </div>
        </form>

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="{{ "/bower_components/admin-lte/plugins/jQuery/jQuery-2.1.4.min.js" }}"></script>
<!-- Bootstrap 3.3.4 -->
<script src="{{ "/bower_components/admin-lte/bootstrap/js/bootstrap.min.js" }}"></script>
<!-- iCheck -->
<script src="{{ "/bower_components/admin-lte/plugins/iCheck/icheck.min.js" }}" ></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>


</body>
</html>
