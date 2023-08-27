@extends('layout')

@section('title')
    Nuevo Usuario Bahia
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Usuarios Bahia
            <small>Creación de nuevo usuario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuario Bahia</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'usuarios_bahia.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoUsuario-form']) !!}
                        @include('usuarios_bahia.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#nuevoUsuario-form').validate({
        rules: {
            "nombre": {
                required: true,
            },
            "ci": {
                required: true,
            },
        },
        messages: {
            "nombre": {
                required: "Ingrese el nombre",
            },
            "ci": {
                required: "Ingrese el nro de C.I.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection