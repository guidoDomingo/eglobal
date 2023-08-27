@extends('app')
@section('title')
    Compra de saldo Epin
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Compra de saldo Epin
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Compras de saldo Epin</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        @if (Sentinel::hasAccess('compra_tarex.add'))
            <div class="box-header">
                <div class="row">
                    <div class="col-md-1">
                        <a href="{{ route('compra_tarex.create') }}" class="btn btn-primary btn-sm" role="button">
                            <span class="fa fa-plus"></span> &nbsp; Agregar
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="box">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filtros de búsqueda</h3>
    
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <form action="{{route('compra_tarex.search')}}" method="GET">
                        <div class="box-body" style="display: block;">
                            
                            <div class="row">
                                <!-- /.col -->
                                <div class="col-md-6">
                                    <!-- Date and time range -->
                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{$reservationtime or ''}}" />
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <!-- /.form group -->
                                    <div class="form-group">
                                        {!! Form::label('modalidad', 'Modalidad') !!}
                                        {!! Form::select(
                                            'modalidad', 
                                            array(
                                                2 => 'Todos', 
                                                0 => 'Contado', 
                                                1 => 'Credito',
                                            ), 
                                            null,
                                            ['class' => 'form-control select2']) !!}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                        </div>
                                    </div>
                                </div>
                            
                            </div>
                            <!-- /.row -->
                        </div>
                        {{--<div class="box-tools">
                            <div class="input-group" style="width:200px; float:right; padding-right:10px">
                                {!! Form::model(Request::only(['context']),['route' => 'reporting.resumen_miniterminales.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                                {!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off' ]) !!}
                                {!! Form::close()!!}
                            </div>
                        </div>--}}
    
                        <!-- /.box-body -->
                        <div class="box-footer" style="display: block;">
                        </div>
                    </form>
                </div>
            </div>
            <br>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($compras)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Nro. de factura</th>
                                    <th>Fecha</th>
                                    <th>Timbrado</th>
                                    <th>Forma de pago</th>
                                    <th>Modalidad</th>
                                    <th>Producto</th>
                                    <th>Costo</th>
                                    <th>Cantidad</th>
                                    <th>Monto</th>
                                    <th style="width:150">Creado</th>
                                    <th style="width:150">Status Ondanet</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($compras as $compra)
                                    <tr data-id="{{ $compra->id  }}">
                                        <td>{{ $compra->id }}.</td>
                                        <td>{{ $compra->numero_factura }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($compra->fecha)) }}</td>
                                        <td>{{ $compra->timbrado }}</td>
                                        @if ($compra->forma_pago =='ATL')
                                            <td>Anticipo a Telecel</td>
                                        @else
                                            <td>Cheques Emitidos</td>
                                        @endif
                                        <td>{{ $compra->modalidad }}</td>
                                        <td>{{ $compra->producto }}</td>
                                        <td>{{ number_format($compra->costo, 2) }}</td>
                                        <td>{{ number_format($compra->cantidad,3) }}</td>
                                        <td>{{ number_format($compra->monto,3) }}</td>
                                        <td>{{ $compra->createdBy->description }}</td>
                                        <td>{{ $compra->status_ondanet }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $compras->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $compras->appends(Request::only(['id']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['compra_tarex.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection

@section('js')

    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- InputMask -->
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>

    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <script type="text/javascript">
        $('.select2').select2();

        $("#export").click(function() {
            if ($('#json').val() !== null && $('#json').val() !== '') {
                $('#form_export').submit();
            } else {
                swal({
                    title: 'Atención',
                    text: 'La lista no tiene registros para exportar.',
                    type: 'warning',
                    showCancelButton: false,
                    closeOnConfirm: true,
                    closeOnCancel: false,
                    confirmButtonColor: '#2778c4',
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        var valuee=$('#reservationtime').val();
            if($('#reservationtime').val() == '' || $('#reservationtime').val() == 0){
                var date = new Date();
                var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

                var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
                var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';
                //$('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
                var valuee=$('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
            }

            //Date range picker
            $('#reservation').daterangepicker();
            $('#reservationtime').daterangepicker({
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Ultimos 7 Dias': [moment().subtract(6, 'days'), moment()],
                    'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()],
                    'Mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    applyLabel: 'Aplicar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Rango Personalizado',
                    daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie','Sab'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                },

                format: 'DD/MM/YYYY HH:mm:ss',
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
            });
    </script>
@endsection
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