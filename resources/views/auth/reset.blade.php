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
        <p class="login-box-msg">Cambio de Contrase&ntilde;a</p>
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Problemas</strong> para cambiar la contrase&ntilde;a
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['route' => ['reset.password'], 'id' => 'login']) !!}
        <div class="form-group has-feeback {{ ($errors->has('password') ? 'has-error' : '') }}">
            {!! Form::password('password', ['class' => 'form-control', 'onclick' => 'directivs()', 'placeholder' =>
            'Ingrese nueva contraseña', 'onClick' => 'directivs()']) !!}

        </div>

        <div class="form-group has-feeback {{ ($errors->has('password2') ? 'has-error' : '') }}">
            {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Confirmar contraseña',
             'required' => "",'onclick' => 'directivs()', ]) !!}
        </div>
        {!! Form::hidden('id', $id) !!}
        {!! Form::hidden('code', $code) !!}

        {!! Form::submit('Aceptar', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div><!-- /.login-box-body -->
    <div  style="text-align: center;color: #444;" >
        <div style="padding-top:30px float: none; margin: 0 auto;">
            <div>
                <div id="directivs" style="height: 18px">

                </div>
                <div id='textdir' class="divdirectivs" style="display: none; ">
                    <h1 class="h1login" style="font-size: 18px">Directivas de Contraseña</h1>
                    <p class="plogin" style="font-size: medium">Ejemplo: parker86</p>
                    <p>
                    @if(config('password.minLength') > 0)
                        <p class="plogin">Cantidad de caractéres mínima:
                            {{ !empty(config('password.minLength')) ? config('password.minLength') : '3' }}</p>
                    @endif
                </div>

            </div>
        </div>
    </div><!-- /.login-box -->
    <div class="login-logo">
        <img src="{{ "/bower_components/admin-lte/dist/img/user7-128x128.jpg" }}" >
    </div>
</div>
<!-- jQuery 2.1.4 -->
<script src="{{ "/bower_components/admin-lte/plugins/jQuery/jQuery-2.1.4.min.js" }}"></script>
<!-- Bootstrap 3.3.4 -->
<script src="{{ "/bower_components/admin-lte/bootstrap/js/bootstrap.min.js" }}"></script>
<!-- iCheck -->
<script src="{{ "/bower_components/admin-lte/plugins/iCheck/icheck.min.js" }}" ></script>
<script>
    var x=0;

    function directivs(){
        if(x==0){
            $('#unnaki').html('');
            $('#directivs').html('<p class="animated fadeIn"><i class="fa fa-spinner fa-pulse" style="font-size: medium"></i></p>');
            setTimeout(function() { makediv(); }, 1200);
            x=1;
        }
    }

    function makediv(){
        $('#directivs').remove();
        document.getElementById('textdir').style.display='block';
        document.getElementById('textdir').className='animated fadeIn';

    }

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
