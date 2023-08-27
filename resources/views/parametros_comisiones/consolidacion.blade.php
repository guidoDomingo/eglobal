@extends('layout')

@section('title')
    Consolidacion de Marcas
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Marcas
            <small>Consolidar Marca</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marcas</a></li>
            <li class="active">consolidar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Consolidar</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'marca.consolidar' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaMarca-form']) !!}
                        <div class="form-group">
                            {!! Form::label('marca', 'Marca Principal') !!}
                            {!! Form::select('marca_id', $marcas_eglobalt, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
                        </div>
                        <div class="form-group">
                            <label>Marcas a consolidar</label>
                            {!! Form::select('marcas_varias_id[]', $marcas, null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'marcas_varias_id',  'style' => 'width:100%', 'data-placeholder' => 'Elija las marcas que desea consolidar a la marca principal']) !!}
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#nuevaMarca-form').validate({
        rules: {
            "descripcion": {
                required: true,
            },
            "categoria_id": {
                required: true,
            },
            "service_source_id": {
                required: true,
            },
        },
        messages: {
            "descripcion": {
                required: "Ingrese la descripción",
            },
            "categoria_id": {
                required: "Seleccione la categoria",
            },
            "service_source_id": {
                required: "Seleccione el service source",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

    // Turn input element into a pond
    FilePond.registerPlugin(
        FilePondPluginFileEncode,
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginFileValidateSize
    );

    $('.filepond').filepond({
        allowMultiple: false
    });
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/filepond/filepond.css" rel="stylesheet">
    <link href="/css/filepond/filepond-plugin-image-preview.css" rel="stylesheet">
@endsection