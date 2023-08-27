@extends('layout')

@section('title')
    Nuevo Formulario
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Formularios
            <small>Creación de nuevo formulario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li><a href="#">Formularios</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'forms.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoForm-form']) !!}
                        @include('forms.partials.fields')
                        <div class="form-row">
                            <a class="btn btn-default" href="{{ route('forms.index',['campaign_id' => $campaign_id]) }}" role="button">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                            {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

<!-- add before </body> -->
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
    $('#nuevoForm-form').validate({
        rules: {
            "label": {
                required: true,
            },
            "data_type": {
                required: true,
            },
            "valorminimo": {
                required: true,
            },
            "valormaximo": {
                required: true,
            },
            "campaigns_id": {
                required: true,
            },
        },
        messages: {
            "label": {
                required: "Ingrese el label del formulario.",
            },
            "data_type": {
                required: "Ingrese un tipo de dato.",
            },
            "valorminimo": {
                required: "Ingrese el valor minimo.",
            },
            "valormaximo": {
                required: "Ingrese el valor maximo.",
            },
            "campaigns_id": {
                required: "Seleccione una campaña/promoción.",
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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
    </style>
@endsection