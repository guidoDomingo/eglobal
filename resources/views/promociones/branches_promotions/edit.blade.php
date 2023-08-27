@extends('layout')

@section('title')
    Sucursal {{ $branchPromotion->name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $branchPromotion->name }}
            <small>Modificación de datos de la sucursal</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('branches_providers.index') }}">Sucursales</a></li>
            <li><a href="#">{{ $branchPromotion->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $branchPromotion->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($branchPromotion, ['route' => ['branches_providers.update', $branchPromotion->id ] , 'method' => 'PUT', 'id' => 'editarBranches-form']) !!}
                            <div class="form-row">

                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('promotions_providers_id', 'Proveedor:') !!} 
                                        {!! Form::select('promotions_providers_id', $providers, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('business_id', 'Negocio:') !!} 
                                    {!! Form::select('business_id', $business, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('name', 'Nombre:') !!}
                                        {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la sucursal' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('address', 'Dirección:') !!}
                                        {!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección' ]) !!}
                                    </div>
                                </div>
                            </div>  
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('phone', 'Teléfono:') !!}
                                        {!! Form::text('phone', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero de telefono' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('provider_branch_id', 'ID Proveedor:') !!}
                                        {!! Form::text('provider_branch_id', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el ID del proveedor' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('latitud', 'Latitud:') !!}
                                        {!! Form::text('latitud', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la latitud' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('longitud', 'Longitud:') !!}
                                        {!! Form::text('longitud', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la longitud' ]) !!}
                                    </div>
                                </div>
                            
                                <div class="form-group col-md-3">
                                    <div class="bootstrap-timepicker">
                                        <div class="form-group">
                                            <label>Horario de atención - Desde:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control timepicker" name="start_time" value="{{$start_time or ''}}"> 
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="bootstrap-timepicker">
                                        <div class="form-group">
                                            <label>Horario de atención - Hasta:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control timepicker" name="end_time"  value="{{$end_time or ''}}">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('location', 'URL de la ubicación (maps):') !!}
                                        {!! Form::text('location', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la url de google maps' ]) !!}
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('custom_image', 'Imágen personalizada (QR)') !!} 
                                            {{-- <small>Formatos soportados : [Vídeo] <b>MP4</b> | Hasta 50 Mb  [Imágen] <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</small> --}}
                                            <h5>Formatos soportados</h5>
                                            <h5>Vídeo: <b>MP4</b> | Hasta 50 Mb</h5>
                                            <h5>Imágen: <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</h5>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3 borderd-content">
                                        <div class="form-group">
                                            <input type="file" class="filepond" name="custom_image" data-max-file-size="50MB" data-max-files="1">
                                            @if(isset($content))
                                                <small style="">Nota: cargar un archivo multimedia solo en caso de querer modificar el actual</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>   

                            </div>  
                                          

                          
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <div class="form-group col-md-3" style="margin-top: 25px;">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <a class="btn btn-default" href="{{ route('branches_providers.index') }}" role="button">Cancelar</a>
                                            </div> 
                                        </div> 
                                    </div> 
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
<!-- add before </body> -->
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>
<script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>

    <!-- add before </body> -->

    <script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <!-- bootstrap time picker -->
    <script src="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    

    //validacion formulario 
    $('#editarBranches-form').validate({
        rules: {
            "name": {
                required: true,
            },
            "address": {
                required: true,
            },
            "latitud": {
                required: true,
            },
            "longitud": {
                required: true,
            },
            "provider_branch_id": {
                required: true,
            },
        },
        messages: {
            "name": {
                required: "Ingrese el nombre de la sucursal.",
            },
            "address": {
                required: "Ingrese una dirección.",
            },
            "latitud": {
                required: "Ingrese la latitud de la sucursal",
            },
            "longitud": {
                required: "Ingrese  la longitud de la sucursal",
            },
            "provider_branch_id": {
                required: "Ingrese  ID del proveedor",
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
     //Timepicker
     $('.timepicker').timepicker({
      showInputs: false,
      //format: 'hh:mm:ss',  
      minuteStep: 10
    })
</script>
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    
    <link href="/css/filepond/filepond.css" rel="stylesheet">
    <link href="/css/filepond/filepond-plugin-image-preview.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.css">

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