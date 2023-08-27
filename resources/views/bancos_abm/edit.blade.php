@extends('layout')

@section('title')
    Banco {{ $banco->descripcion }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $banco->descripcion }}
            <small>Modificación de datos del banco</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('bancos.index') }}">Bancos</a></li>
            <li><a href="#">{{ $banco->descripcion }}</a></li>
            <li class="active">Modificar</li>
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
                        {!! Form::model($banco, ['route' => ['bancos.update', $banco->id ] , 'method' => 'PUT', 'id' => 'editarBanco-form']) !!}
                            <div class="form-row">
                
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('descripcion', 'Nombre del banco') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del banco' ]) !!}
                                    </div>
                                </div>
                                
                            <div class="col-md-12">
                                <a class="btn btn-default" href="{{ route('bancos.index') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                            </div>  

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#editarBanco-form').validate({
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
            title: 'Ocurrió un error al intentar registrar el nombre del campo. Verifique los campos',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif

@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    
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