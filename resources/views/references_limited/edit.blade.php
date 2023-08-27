@extends('layout')

@section('title')
    Referencia
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $reference_limited->reference }}
            <small>Modificación de Referencia</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('references.index') }}">Referencias</a></li>
            <li><a href="#">{{ $reference_limited->reference }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($reference_limited, ['route' => ['references.update', $reference_limited->service_rule_id, $reference_limited->current_params_rule_id, $reference_limited->reference ] , 'method' => 'PUT', 'id' => 'editarReferencia-form']) !!}
                        @include('references_limited.partials.fields')

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
    $('#editarReferencia-form').validate({
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
        allowMultiple: false,
    });
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
@endsection
