@extends('layout')

@section('title')
    Configuración de Alerta - {{ $notifications_params->prefix }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $notifications_params->prefix }}
            <small>Modificación</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('notifications_params.index') }}">Configuración de Alertas</a></li>
            <li><a href="#">{{ $notifications_params->prefix }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar alerta</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($notifications_params, ['route' => ['notifications_params.update', $notifications_params->id ] , 'method' => 'PUT', 'id' => 'editarAlerta-form']) !!}
                        @include('notifications_params.partials.fields')

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
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script src="/js/bootstrap-tagsinput.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#editarAlerta-form').validate({
        rules: {
            "mensaje": {
                required: true,
            },
            "valor": {
                required: true,
            },
            "destinatarios": {
                required: true,
            }
        },
        messages: {
            "mensaje": {
                required: "Ingrese el mensaje",
            },
            "valor": {
                required: "Ingrese el valor del parametro",
            },
            "destinatarios": {
                required: "Debe asignar por lo menos 1 destinatario",
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

    var servicioSeleccionado = '';

    $(document).on('select2:select','#serviceSourceId',function(){
        var valor = $(this).val();

        var urlGetServices = "{{ route('reports.get_service_request_param') }}";
        if(valor != '' && valor != 'null'){
            $.get(urlGetServices, {id: valor}).done(function(data){
                servicioSeleccionado = $('#serviceId').val();
                $('#serviceId').empty().trigger('change');
                $('#serviceId').select2({data: data});
                if(servicioSeleccionado != ''){
                    $('#serviceId').val(servicioSeleccionado).trigger('change');
                }
                if($("#checkbox2").is(':checked') ){
                    $("#serviceId > option").prop("selected","selected");// Select All Options
                    $("#serviceId").trigger("change");// Trigger change to select 2
                }
            });
        }else{
            $('#serviceId').select2('data', null);
            $('.mostrar').hide();
        }
    });

    $(document).on('select2:clear select2:unselect','#serviceSourceId',function(e){
        $("#serviceId").trigger("select2:select");
        if (!e.params.originalEvent) {
            return
        }

        e.params.originalEvent.stopPropagation();
    });

    $(document).on('select2:clear select2:unselect','#serviceId',function(e){
        if (!e.params.originalEvent) {
            return
        }

        e.params.originalEvent.stopPropagation();
    });

    $('#serviceSourceId').trigger('select2:select');

    $('#destinatarios').tagsinput({
        allowDuplicates: false,
        confirmKeys: [13, 44],
        tagClass: 'selector-destinatarios'
    });

    $(document).on('keypress','.bootstrap-tagsinput input', function(e){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap-tagsinput.css">
    <style type="text/css">
        .bootstrap-tagsinput {
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            display: block;
            padding: 4px 6px;
            color: #555;
            vertical-align: middle;
            border-radius: 4px;
            max-width: 100%;
            line-height: 22px;
            cursor: text;
        }
        .bootstrap-tagsinput input {
            border: none;
            box-shadow: none;
            outline: none;
            background-color: transparent;
            padding: 0 6px;
            margin: 0;
            width: auto;
            max-width: inherit;
        }

        .selector-destinatarios {
            color: white;
            background-color: #3d8dbc;
            border: 1px solid #aaa;
            border-radius: 4px;
            cursor: default;
            float: left;
            padding: 0 5px;
        }
    </style>
@endsection
