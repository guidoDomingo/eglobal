@extends('layout')

@section('title')
    Marca {{ $marca->name }}
@endsection

@section('content')
    <section class="content-header">
        <h1>
            {{ $marca->name }}
            <small>Modificación de datos de Marca</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('marca.index') }}">Marcas</a></li>
            <li><a href="#">{{ $marca->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $marca->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                       
                       {!! Form::model($marca, ['url' => route('marca.update', $marca->id), 'method' => 'PUT', 'id' => 'editarMarca-form']) !!}
                        @include('marcas.partials.fields')

                        

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
    $('#editarMarca-form').validate({
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


    var imagen = '{{ trim($marca->imagen_asociada) }}'
    const inputElement = document.querySelector('input[type="file"]');
    const pond = FilePond.create(inputElement,{
    files: [
        {
            source: '{{ trim($marca->imagen_asociada) }}',
        }
    ]
});
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
@endsection
