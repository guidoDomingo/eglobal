@extends('layout')

@section('title')
    Nueva Etiqueta
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Etiquetas
            <small>Creación de etiqueta</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li><a href="#">Etiquetas</a></li>
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
                        {!! Form::open(['route' => 'tags.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoTag-form']) !!}
                        @include('tags.partials.fields')
                        <div class="form-row">
                            <a class="btn btn-default" href="{{ route('tags.index',['ticket_id' => $ticket_id,'campaign_id' => $campaign_id ]) }}" role="button">Cancelar</a>
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
    $('#nuevoTag-form').validate({
        rules: {
            "description": {
                required: true,
            },
            "value": {
                required: true,
            },

        },
        messages: {
            "description": {
                required: "Ingrese un header.",
            },
            "value": {
                required: "Ingrese un footer.",
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