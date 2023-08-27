@extends('layout')

@section('title')
    Grilla de Servicios
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Grilla de Servicios
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marcas</a></li>
            <li class="active">Grilla de Servicios</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filtrar por marca</h3>
                        <div class="box-tools">
                            <div class="input-group" style="width:350px;">
                                {!! Form::model(Request::only(['name']),['route' => 'marca.grilla_servicios', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                                <input type="hidden" name="atm_id" value="{{ $atm_id }}">
                                {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Filtrar por Marca', 'autocomplete' => 'off' ]) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        <div class="row">
                            @if(empty($data) && !empty($atm_id))
                                @if(!empty(request()->get('name')))
                                    <div class="pad margin no-print">
                                        <div class="callout callout-warning" style="margin-bottom: 0!important;">
                                            <h4><i class="fa fa-info"></i> Atención:</h4>
                                            El ATM #{{ $atm_id }} no posee ningún servicio similiar a <strong> {{ request()->get('name') }} </strong>. <br>
                                        </div>
                                    </div>
                                @else
                                    <div class="pad margin no-print">
                                        <div class="callout callout-warning" style="margin-bottom: 0!important;">
                                            <h4><i class="fa fa-info"></i> Atención:</h4>
                                            El ATM #{{ $atm_id }} no posee ningún servicio asociado. <br>
                                            Puede tomar como base la <strong>Grilla General de Servicios</strong>, o puede seleccionar un <strong>ATM con una grilla asociada</strong>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="col-md-6">
                                {!! Form::model(Request::only(['name']),['route' => 'marca.grilla_servicios', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search', 'id' => 'atmSearch']) !!}
                                {!! Form::label('atm_id', 'Seleccione un ATM si desea personalizar su grilla de servicios') !!} <br>
                                @if(\Sentinel::getUser()->hasAccess('marca.activar_marca_grilla'))
                                    @if(!empty($atm_id)) <a href="javascript:void(0)" data-toggle="modal" data-target="#marca-adicional"> <i class="fa fa-plus"></i> Añadir marcas adicionales al ATM</a> @endif
                                @endif
                                
                                {!! Form::select('atm_id', $atms, $atm_id, ['id' => 'atmId','class' => 'select2', 'style' => 'width:100%', 'placeholder' => 'Seleccione un ATM']) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                        <br>

                        @if(empty($data) && !empty($atm_id))
                            @if(empty(request()->get('name')))
                                {!! Form::open(['route' => 'marca.grilla_servicios_atm_store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaMarca-form']) !!}
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::model(Request::only(['name']),['route' => 'marca.grilla_servicios', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search', 'id' => 'atmSearch']) !!}
                                        {!! Form::label('atm_base_id', 'ATM\'s con grilla de servicios asociada') !!} <br>
                                        {!! Form::select('atm_base_id', $atms_base, null, ['id' => 'atmBaseId','class' => 'select2', 'style' => 'width:100%', 'placeholder' => 'Seleccione un ATM para tomar como base']) !!}
                                    </div>
                                    <input type="hidden" name="atm_id" value="{{ $atm_id }}">
                                </div>
                                <br>
                                <button type="submit" class="btn btn-primary">Aplicar</button>
                                {!! Form::close() !!}
                            @endif
                        @else
                        {!! Form::open(['route' => 'marca.grilla_servicios_store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaMarca-form']) !!}
                        {{-- <div class="transfer"></div> --}}
                        @foreach($data as $marca)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box box-default">
                                    <div class="box-header with-border" >
                                        <div data-widget="collapse">
                                            <i class="fa fa-minus" data-toggle="tooltip" title="Minimizar"></i>
                                            @if(strstr($marca['imagen'], 'http'))
                                                <img class="imagen_marcas_servicios" src="{{ $marca['imagen'] }}">
                                            @else
                                                @if(base64_encode(base64_decode($marca['imagen'], true)) === $marca['imagen'] && !empty($marca['imagen']))
                                                    <img class="imagen_marcas_servicios" src="data:image/png;base64,{{ $marca['imagen'] }}">
                                                @elseif(file_exists(public_path().'/resources'.trim($marca['imagen'])) && !empty($marca['imagen']))
                                                    <img class="imagen_marcas_servicios" src="{{ url('/resources'.$marca['imagen']) }}">
                                                @endif
                                            @endif
                                            <h3 class="box-title"><strong> {{ $marca['name'] }} #{{ $marca['id'] }}</strong></h3>
                                        </div>

                                        @if(\Sentinel::getUser()->hasAccess('marca.quitar_marca_grilla'))
                                            @if(!empty($atm_id))
                                                <a href=" {{ route('marca.quitar_marca_atm', ['marca_id' => $marca['id'], 'atm_id' => $atm_id]) }} "><i class="fa fa-trash"></i> Quitar marca</a>
                                            @endif
                                        @endif
                                        <!-- <div class="box-tools">
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Minimizar"><i class="fa fa-minus"></i></button>
                                        </div> -->
                                    </div>
                                    <div class="box-body">
                                        <select multiple="multiple" name="servicios[{{ $marca['id'] }}][]" class="demo2" id="{{ $marca['id'] }}">
                                            @foreach($marca['servicios'] as $servicio)
                                                <option value="{{ $servicio['id'] }}" @if($servicio['selected']) selected @endif>#{{ $servicio['service_id'] }} {{ $servicio['name'] }} </option>
                                            @endforeach
                                        </select>
                                        <small><i class="fa fa-info"></i> Puede seleccionar más de un servicio manteniendo pulsado <strong>Ctrl</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if(!empty($atm_id))
                            <input type="hidden" name="atm_id" value="{{ $atm_id }}">
                        @endif
                        @if(\Sentinel::getUser()->hasAnyAccess('marca.guardar_grilla', 'marca.guardar_grilla_general'))
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        @endif
                        {!! Form::close() !!}
                        @endif
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">{{ $marcas->total() }} registros en total
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $marcas->appends(Request::only(['name','atm_id']))->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="marca-adicional" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Marcas Disponibles<label class="idTransaccion"></label></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 marcas_nuevas">
                                <input type="text" name="" id="search"  class="form-control" placeholder="Buscar Marca">
                                <div class="row">
                                    <br>
                                    @foreach($marcas_no_asociadas as $marca)
                                        <div class="col-md-6 data-marca">
                                            <div class="marca_activa">
                                                @if(strstr($marca->imagen_asociada, 'http'))
                                                    <img class="imagen_marcas_servicios" src="{{ $marca->imagen_asociada }}">
                                                @else
                                                    @if(base64_encode(base64_decode($marca->imagen_asociada, true)) === $marca->imagen_asociada && !empty($marca->imagen_asociada))
                                                        <img class="imagen_marcas_servicios" src="data:image/png;base64,{{ $marca->imagen_asociada }}">
                                                    @elseif(file_exists(public_path().'/resources'.trim($marca->imagen_asociada)) && !empty($marca->imagen_asociada))
                                                        <img class="imagen_marcas_servicios" src="{{ url('/resources'.$marca->imagen_asociada) }}">
                                                    @endif
                                                @endif
                                                <span>
                                                    <span class="descripcion-marca">
                                                        {{ $marca->descripcion }} #{{ $marca->id }} 
                                                    </span>
                                                    <label class="switch">
                                                        <input type="checkbox" class="activar_marca" marca-id='{{ $marca->id }}'>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                        <button type="button" style="display: none" class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                        <button type="buttom" style="display: none" class="devolucion btn btn-primary pull-left">Devolución</button>

                        <!--para ejecutar tareas de reproceso o devolucion -->
                        <button type="buttom" style="display: none" id="process_devolucion" class="btn btn-primary pull-left">Enviar a devolución</button>
                        <button type="button" style="display: none" id="run_reprocesar"class="btn btn-primary pull-left">Enviar a Reprocesar</button>
                        <!--para Cancelar sin hacer nada -->
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('js')
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script src="/js/dual_listbox/jquery.transfer.js"></script>
<script src="/js/bootstrap_dualist/jquery.bootstrap-duallistbox.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
    $(function(){
        var reload_data = false;
        $('.demo2').bootstrapDualListbox({
            nonSelectedListLabel: '<i class="fa fa-circle text-danger"></i> Servicios Deshabilitados',
            selectedListLabel: '<i class="fa fa-circle text-success"></i> Servicios Habilitados',
            preserveSelectionOnMove: 'all',
            filterPlaceHolder: 'Filtre por servicio',
            moveSelectedLabel: 'Mover seleccionado',
            moveAllLabel: 'Mover todo',
            removeSelectedLabel: 'Quitar seleccionado',
            removeAllLabel: 'Quitar todo',
            infoText: 'Mostrando {0}',
            infoTextFiltered: '<span class="label label-warning">Filtrado</span> {0} de {1}',
            moveOnSelect: false,
            infoTextEmpty: 'Lista vacía',
            helperSelectNamePostfix: '[valores]',
            // nonSelectedFilter: 'ion ([7-9]|[1][0-2])',
            filterTextClear: 'Mostrar todo'
        });

        $('.select2').select2();

        //validacion formulario 
        $('#nuevaMarca-form').validate({
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            }
        });

        $(document).on('change','#atmId',function(){
            $('#atmSearch').submit();
        });

        var tabla = $('#marcas_adicionales').DataTable({
            "language": {
                "url": "/bower_components/admin-lte/plugins/datatables/spanish.json",
            }
        });

        $('#search').on('keyup',function () {
            var filter = $(this).val().toLowerCase(); // get the value of the input, which we filter on
            $('.marcas_nuevas').find(".data-marca").filter(function() {
                console.log($(this).find('.descripcion-marca').text().toLowerCase());
                $(this).toggle($(this).find('.descripcion-marca').text().toLowerCase().indexOf(filter) > -1);
            });
        });

        $('.activar_marca').on('change',function(e){
            reload_data = true;
            e.preventDefault();
            let row = $(this).parents('tr');
            let atm_id = '{{ $atm_id }}';
            let marca_id = $(this).attr('marca-id');
            let value = null;
            let mensaje =  '';
            var checkeado = $(this).is(':checked');
            var thisAtm = $(this);

            if( $(this).is(':checked') ){
                // Hacer algo si el checkbox ha sido seleccionado
                value = true;
                mensaje = 'Marca habilitada'
            } else {
                // Hacer algo si el checkbox ha sido deseleccionado
                value = false;
                mensaje = 'Marca desactivada'
            }

            $.post("{{ route('marca.activar_marca') }}", {
                _token: token, 
                _atm_id : atm_id, 
                _marca_id : marca_id, 
                _value : value 
            }, function( data ) {
                if(data.error){
                    thisAtm.prop('checked', !checkeado);
                    alert('Ha ocurrido un error');
                }
                
                console.log('Solicitud procesada '+mensaje);
            });
        });

        $('#marca-adicional').on('hidden.bs.modal', function(){
            if(reload_data){
                location.reload(true);
            }
        })
    });

</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/icon_font/css/icon_font.css">
    <link rel="stylesheet" href="/css/dual_listbox/jquery.transfer.css">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap_dualist/bootstrap-duallistbox.css">
    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display:  inline-block;
            width:    30px;
            height:   17px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
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
            height: 13px;
            width: 13px;
            left: 2px;
            bottom: 2px;
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
            -webkit-transform: translateX(13px);
            -ms-transform: translateX(13px);
            transform: translateX(13px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .imagen_marcas_servicios {
            vertical-align:middle;
            /*border-radius: 0% !important;
            margin-left: auto;
            margin-right: auto;
            display: block;*/
        }

        .marca_activa {
            margin-top: 2px;
            padding:  5px;
            border-bottom: 1px solid #cccccc;
            /*border-radius: 8px;*/
        }

    </style>

@endsection