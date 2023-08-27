@extends('layout')

@section('title')
    Voucher {{ $voucher->coupon_code }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $voucher->coupon_code }}
            <small>Modificación de datos del voucher</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('promotions_vouchers.index') }}">Vouchers</a></li>
            <li><a href="#">{{ $voucher->coupon_code }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $voucher->title }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($art, ['route' => ['promotions_vouchers.update', $art->id ] , 'method' => 'PUT', 'id' => 'editarVoucher-form']) !!}
                        {{-- @include('promotion_vouchers.partials.fields') --}}
                        <div class="form-row">
                            <a class="btn btn-default" href="{{ route('promotions_vouchers.index') }}" role="button">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
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
    $('#editarVoucher-form').validate({
        rules: {
            "title": {
                required: true,
            },
            "image": {
                required: true,
            },
            "duracionReprodu": {
                required: true,
            },
            "duracionPausa": {
                required: true,
            },
            "campaigns_id": {
                required: true,
            },
        },
        messages: {
            "title": {
                required: "Ingrese el titulo del arte.",
            },
            "image": {
                required: "Ingrese una url para la imagen asociada.",
            },
            "duracionReprodu": {
                required: "Ingrese la duración de la reproducción.",
            },
            "duracionPausa": {
                required: "Ingrese la duración de la pausa.",
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