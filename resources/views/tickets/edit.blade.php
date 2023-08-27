@extends('layout')

@section('title')
    Ticket {{ $ticket->label }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $ticket->label }}
            <small>Modificación de datos del ticket</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('tickets.index') }}">Tickets</a></li>
            <li><a href="#">{{ $ticket->label }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $ticket->label }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($ticket, ['route' => ['tickets.update', $ticket->id ] , 'method' => 'PUT', 'id' => 'editarTicket-ticket']) !!}
                        @include('tickets.partials.fields')
                        <div class="ticket-row">
                            <a class="btn btn-default" href="{{ route('tickets.index',['campaign_id' => $campaign_id]) }}" role="button">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
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

    //validacion ticket 
    $('#editarTicket-ticket').validate({
        rules: {
            "header": {
                required: true,
            },
            "footer": {
                required: true,
            },
            "campaigns_id": {
                required: true,
            },
        },
        messages: {
            "header": {
                required: "Ingrese un header.",
            },
            "footer": {
                required: "Ingrese un footer.",
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
        allowMultiple: false,
    });
           
</script>
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

    </style>
@endsection