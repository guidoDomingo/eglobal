@extends('layout')

@section('title')
    Nuevo Descuento por Comision
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Descuento por Comision
            <small>Creación de Descuento por Comision</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Descuento por Comision</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        {!! Form::open(['route' => 'recibos_comisiones.store' , 'method' => 'POST', 'role' => 'form']) !!}
            <div id="myModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Detalles - Cliente : <label class="group_description"></label></h4>
                        </div>
                        <div class="modal-body">
                            <table id="detalles" class="table table-bordered table-hover dataTable" role="grid"
                                aria-describedby="Table1_info">
                                <thead>
                                    <tr role="row">
                                        <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                        <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" width="120px">Tipo</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" width="150px">Descripcion</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1">Monto a debitar</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1">Deuda Actual</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1">Deuda Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-contenido">
                                    
                                </tbody>
                                <div style="display: none">
                                    {!! Form::text('total_monto', 0, ['id' => 'total_monto','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                    {!! Form::text('total_alquiler', 0, ['id' => 'total_alquiler','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                    {!! Form::text('total_venta', 0, ['id' => 'total_venta','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                    {!! Form::text('total_transacciones', 0, ['id' => 'total_transacciones','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                    {!! Form::text('id_atm', 0, ['id' => 'id_atm','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                </div>
                                {{--<tfoot>
                                    <tr>
                                        <th style="display:none;" rowspan="1" colspan="1"></th>
                                        <th style="display:none;" rowspan="1" colspan="1"></th>
                                        <th rowspan="1" colspan="1">Tipo</th>
                                        <th rowspan="1" colspan="1">Descripcionn</th>
                                        <th rowspan="1" colspan="1">Monto</th>
                                    </tr>
                                </tfoot>--}}
                            </table>
                            <h4><label id="mensaje_deuda"></label></h4>
                        </div>
                        <div class="modal-footer">
                            <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                            <button type="button" style="display: none"
                                class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                            <button type="buttom" style="display: none"
                                class="devolucion btn btn-primary pull-left">Devolución</button>
        
                            <!--para ejecutar tareas de reproceso o devolucion -->
                            <button type="buttom" style="display: none" id="process_devolucion"
                                class="btn btn-primary pull-left">Enviar a devolución</button>
                            <button type="button" style="display: none" id="run_reprocesar"
                                class="btn btn-primary pull-left">Enviar a Reprocesar</button>
        
                            <!--para ejecutar inconsistencia -->
                            <button type="button" style="display: none" class="inconsistencia btn btn-primary pull-left">Generar
                                inconsistencia</button>
                            <button type="submit" style="display: none" id="process_comision"
                                class="btn btn-primary pull-left">Aceptar</button>
        
                            <!--para ejecutar reversiones ken -->
                            <button type="button" style="display: none" class="reversion_ken btn btn-primary pull-left">Generar
                                Reversion Ken</button>
                            <button type="buttom" style="display: none" id="process_reversion_ken"
                                class="btn btn-primary pull-left">Generar Reversion</button>
                            <!--para Cancelar sin hacer nada -->
                            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
        
                </div>
            </div>
        {!! Form::close() !!}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Descuento por Comision</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        @include('recibos_comisiones.partials.fields')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page_scripts')
    <script src="/js/filepond/filepond-plugin-image-preview.js"></script>
    <script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
    <script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
    <script src="/js/filepond/filepond-plugin-file-encode.js"></script>
    <script src="/js/filepond/filepond.min.js"></script>
    <script src="/js/filepond/filepond.jquery.js"></script>
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>
    <script src="/js/bootstrap-tagsinput.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

@endsection    

@section('page_scripts')
    @include('partials._selectize')
    <script>
        $('.select2').select2();

        //Date range picker
        $('#last_update').datepicker({
            language: 'es',
            format: 'yyyy-mm-dd 00:00:00',
        });

        $('#managers').change(function(){                
            if(this.value == 0)
            {
                $("#block_form").css("display", "block");                    
            }else
            {
                $("#block_form").css("display", "none");                    
            } 
        });

        $("#amount").on({

            "focus": function (event) {
                $(event.target).select();
            },
            "keyup": function (event) {
                $(event.target).val(function (index, value ) {
                    return value.replace(/\D/g, "")
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                });
            }
        });

        $('.info').on('click', function(e) {
            e.preventDefault();
            var atm_id = $('#atm_id').val();
            var monto = $('#amount').val();

            console.log('atm_id: '+ atm_id +" y monto: "+ monto);
            $.get('/recibos_comisiones/balance/' + atm_id + '/' + monto, function(data) {
                console.log(data);
                $(".group_description").html(data['grupo']);
                
                $("#payment_details").hide();
                if(data['descontar_deuda']){
                    $("#detalles").show();
                    $("#modal-contenido").html(data['payment_info']);
                    $("#mensaje_deuda").hide();
                    $('#process_comision').show();
                    $('#total_monto').val(data['total_comision']);  
                    $('#total_monto').trigger('change.select2');
                    $('#total_alquiler').val(data['details']['alquiler'] );  
                    $('#total_alquiler').trigger('change.select2');
                    $('#total_venta').val(data['details']['ventas'] );  
                    $('#total_venta').trigger('change.select2');
                    $('#total_transacciones').val(data['details']['transaccionado']);  
                    $('#total_transacciones').trigger('change.select2');
                    $('#id_atm').val(data['atm_id']);  
                    $('#id_atm').trigger('change.select2');
                }else{
                    $('#process_comision').hide();
                    $("#mensaje_deuda").show();
                    $("#mensaje_deuda").html('El monto sobrepasa la deuda a debitar');
                    $("#detalles").hide();
                }
                $("#myModal").modal();
                //botones
                $('.devolucion').hide();
                $('.reprocesar').hide();
                $('#process_devolucion').hide();
                $('.inconsistencia').hide();
                $('.reversion_ken').hide();
                $('#process_reversion_ken').hide();
                $('#run_reprocesar').hide();
            });
        });

    </script>
@append

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap-tagsinput.css">
    <link href="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.css" rel="stylesheet" type="text/css" />
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
        .selector-serialnumber {
            color: white;
            background-color: #3d8dbc;
            border: 1px solid #aaa;
            border-radius: 4px;
            cursor: default;
            float: left;
            padding: 0 5px;
        }

        /* Optional theme */

        /*@import url('//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css');*/
        .stepwizard-step p {
            margin-top: 0px;
            color:#666;
        }
        .stepwizard-row {
            display: table-row;
        }
        .stepwizard {
            display: table;
            width: 100%;
            position: relative;
        }
        .stepwizard-step button[disabled] {
            /*opacity: 1 !important;
            filter: alpha(opacity=100) !important;*/
        }
        .stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
            opacity:1 !important;
            color:#bbb;
        }
        .stepwizard-row:before {
            top: 14px;
            bottom: 0;
            position: absolute;
            content:" ";
            width: 100%;
            height: 1px;
            background-color: #ccc;
            z-index: 0;
        }
        .stepwizard-step {
            display: table-cell;
            text-align: center;
            position: relative;
        }
        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }

        /* animacion del boton al guardar */
        .spinner {
          display: inline-block;
          opacity: 0;
          width: 0;

          -webkit-transition: opacity 0.25s, width 0.25s;
          -moz-transition: opacity 0.25s, width 0.25s;
          -o-transition: opacity 0.25s, width 0.25s;
          transition: opacity 0.25s, width 0.25s;
        }

        .has-spinner.active {
          cursor:progress;
        }

        .has-spinner.active .spinner {
          opacity: 1;
          width: auto; /* This doesn't work, just fix for unkown width elements */
        }

        .has-spinner.btn-mini.active .spinner {
            width: 10px;
        }

        .has-spinner.btn-small.active .spinner {
            width: 13px;
        }

        .has-spinner.btn.active .spinner {
            width: 16px;
        }

        .has-spinner.btn-large.active .spinner {
            width: 19px;
        }
    </style>
@endsection
@include('alquiler.partials.modal_group')