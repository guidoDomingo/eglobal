@extends('layout')

@section('title')
    Departamento {{ $departamento->descripcion }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $departamento->descripcion }}
            <small>Modificación de datos del Departamento</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('departamentos.index') }}">Departamento</a></li>
            <li><a href="#">{{ $departamento->descripcion }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $departamento->descripcion }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($departamento, ['route' => ['departamentos.update', $departamento->id ] , 'method' => 'PUT', 'id' => 'editarDepartamento-form']) !!}
                        @include('departamentos.partials.fields')

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
{{--                    @include('marcas.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
<!-- add before </body> -->
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#editarDepartamento-form').validate({
        rules: {
            "descripcion": {
                required: true,
            },
        },
        messages: {
            "descripcion": {
                required: "Ingrese la descripción",
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
