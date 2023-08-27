@extends('layout')

@section('title')
    Nuevo Banco
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Bancos
            <small>Creación de nuevo banco</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de clientes</a></li>
            <li><a href="#">Bancos</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <small style="color: red"><strong>Campos obligatorios (*)</strong></small>
                    </div>
                    <div class="box-body">
                        {{-- @include('partials._flashes') --}}
                        @include('partials._messages')
                        {!! Form::open(['route' => 'bancos.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoBanco-form']) !!}
                        @include('bancos_abm.partials.fields')
                        {{-- <button type="submit" class="btn btn-primary">Guardar</button> --}}
                    
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
<!-- bootstrap time picker -->
<script src="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();


    //validacion formulario 
    $('#nuevoBanco-form').validate({
        rules: {
            "descripcion": {
                required: true,
            },
        },
        messages: {
            "descripcion": {
                required: "Ingrese el nombre del banco.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });


</script>
@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar registrar el contenido. Verifique los campos',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/filepond/filepond.css" rel="stylesheet">
    <link href="/css/filepond/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  <link rel="stylesheet" href="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.css">

    <style>
        label span {
            font-size: 1rem;
        }

        label.error {
            color: red;
            font-size: 1rem;
            display: block;
            margin-top: 5px;
        }

        input.error {
            border: 1px dashed red;
            font-weight: 300;
            color: red;
        }
     
        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 300px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
    </style>


@endsection
