@extends('layout')

@section('title')
    Campaña {{ $campaign->name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $campaign->name }}
            <small>Modificación de datos de la campaña</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li><a href="{{ route('campaigns.index') }}">Campañas</a></li>
            <li><a href="#">{{ $campaign->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar: {{ $campaign->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($campaign, ['route' => ['campaigns.update', $campaign->id ] , 'method' => 'PUT', 'id' => 'editarCampaña-form']) !!}
                            {{-- @include('campaigns.partials.fields') --}}
                            <div class="form-row">
                                <div class="form-group col-md-12 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-cogs"></i> Configuración de la campaña &nbsp;</h4></div>
                                    <div class="container-campaing">
                                        <div class="form-group col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('name', 'Nombre de la camapaña') !!}
                                                {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la campaña' ]) !!}
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('start_date', 'Duración de la campaña') !!}
                                                <input id="reservationtime" type="text" name="reservationtime" class="form-control" value="{{$datetime or ''}}"/>     
                                            </div>
                                            <div class="form-group text-left">
                                                {!! Form::label('code_generate', 'Generación de código o voucher') !!}
                                            </div>
                                            <div class="radio">
                                                {!! Form::radio('code_generate', 'text',true,['style' => 'margin-left:150px']) !!}
                                                {!! Form::label('code_generate', 'Texto') !!}
                                                <br>
                                                {!! Form::radio('code_generate', 'qr',null,['style' => 'margin-left:150px']) !!}
                                                {!! Form::label('code_generate', 'Código QR') !!}
                                                <br>
                                                {!! Form::radio('code_generate', 'barcode',null,['style' => 'margin-left:150px']) !!}
                                                {!! Form::label('code_generate', 'Código de barra') !!}            
                                            </div>
                                        </div>
                                    
                                        <div class="form-group col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('flow', 'Flujo') !!}
                                                {!! Form::select('flow', ['1' => 'Inicio de la transacción','2' => 'Durante la transacción', '3' => 'Al finaliza la transacción'],$flow_id ,['placeholder' => 'Seleccione una opción','style' => 'width: 100%; height:35px; ', 'id' => 'flow']) !!}
                                            </div>
                                            <div class="form-group">
                                                {!! Form::label('tipoCampaña', 'Tipo de campaña') !!}
                                                {!! Form::select('tipoCampaña', ['1' => 'Campaña informativa','2' => 'Promoción de productos', '3' => 'Promoción + venta de productos'],null ,['placeholder' => 'Seleccione una opción','style' => 'width: 100%; height:35px;', 'id' => 'tipoCampaña']) !!}
                                            </div>
                                        </div>                                      

                                        @if(isset($campaign))
                                            <div class="form-group">
                                                {!! Form::label('perpetuity', 'Opción de perpetuidad') !!} &nbsp;
                                                <label class="switch">
                                                    {!! Form::checkbox('perpetuity', 1, $campaign->perpetuity ? true : false) !!}<span class="slider round"></span>
                                                </label>
                                            </div>
                                        @else
                                            <div class="form-group">
                                                {!! Form::label('perpetuity', 'Opción de perpetuidad') !!} &nbsp;
                                                <label class="switch">
                                                    <input type="checkbox" name="perpetuity" id="perpetuity"><span class="slider round"></span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-row">
                                <div class="form-group col-md-12 borderd-content-2">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-tags"></i> Contenidos a promocionar &nbsp;</h4></div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="content">
                                                <br>
                                                {!! Form::label('content_id', 'Contenido') !!} 
                                                {!! Form::text('contents', !empty($contentsIds) ? $contentsIds : null, ['class' => 'form-control input-lg', 'id' => 'selectContents', 'placeholder' => 'Seleccione un Contenido']) !!}
                                            </div>
                                        </div>
                                    </div>   
                                </div>  
                            </div>

                            <div class="col-md-12">
                                <a class="btn btn-default" href="{{ route('campaigns.index') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
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

    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
    <script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script type="text/javascript">
        $('.btn-delete').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            swal({
                title: "Atención!",
                text: "Está a punto de borrar el registro, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var form = $('#form-delete');
                    var url = form.attr('action').replace(':ROW_ID',id);
                    var data = form.serialize();
                    var type = "";
                    var title = "";
                    $.post(url,data, function(result){
                        if(result.error == false){
                            row.fadeOut();
                            type = "success";
                            title = "Operación realizada!";
                        }else{
                            type = "error";
                            title =  "No se pudo realizar la operación"
                        }
                        swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                    }).fail(function (){
                        swal('No se pudo realizar la petición.');
                    });
                }
            });
        });
    </script>
    <script type="text/javascript">

        //Date range picker
        $('#reservationtime').daterangepicker({
                opens: 'right',
                locale: {
                    applyLabel: 'Aplicar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Rango Personalizado',
                    daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie','Sab'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                },
                format: 'DD/MM/YYYY',
                //startDate: moment(),
               // endDate: moment().add(1,'months'),
            });

        $('.select2').select2();

        //validacion formulario 
        $('#editarCampaña-form').validate({
            rules: {
                "name": {
                    required: true,
                },
                "reservationtime": {
                    required: true,
                },
                "flow": {
                    required: true,
                },
                "tipoCampaña": {
                    required: true,
                },
            },
            messages: {
                "name": {
                    required: "Ingrese el nombre de la campaña/promoción.",
                },
                "reservationtime": {
                    required: "Seleccione un rango de fecha.",
                },
                "flow": {
                    required: "Seleccione el flujo de la campaña.",
                },
                "tipoCampaña": {
                    required: "Seleccione el tipo de campaña/promoción.",
                },
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            }
        });    

        //Date range picker
        $('.reservationtime').datepicker({
                changeMonth: true,
                changeYear: true,
                language: 'es',
                format: 'yyyy/mm/dd',
                firstDay: 1
        });   

        $('#selectContents').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            render: {
                item: function(item, escape) {
                    return '<div><span class="label label-primary">' + escape(item.name) + '</span></div>';
                }
            },
            options: {!! $contentsJsonAll !!}
        });
        


    </script>

     
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <style>
        /* radio style */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
    <style>
        /* Errors Styles */
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
    <style>
        /* border style */
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
            height: 370px;
            margin-top: 20px;
            position: relative;
            height: auto;

        }

        .borderd-content-inside {
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

        .borderd-content .content {
            padding: 10px;
        }

        .borderd-content-2 {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
            margin-top: 20px;
            position: relative;

        }
        .borderd-content-2 .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        
        .borderd-campaing {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 150px;
            margin-top: 20px;
            position: relative;
            height: auto;

        }
        .borderd-campaing .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .container-campaing {
            margin-top: 20px;
        }
  
    </style>
@endsection
