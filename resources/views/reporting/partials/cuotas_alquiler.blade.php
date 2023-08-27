<section class="content">
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles - Transaccion ID : <label class="idTransaccion"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Detalles del Rechazo</th>
                        </tr>
                        </thead>
                        <tbody id="modal-contenido">

                        </tbody>
                    </table>
                    <div id="status_description"></div>
                    <table id="payment_details" style="display: none;" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table2_info">                                             <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor a pagar</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor recibido</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor devuelto</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha</th>
                        </tr>
                        </thead>
                        <tbody id="modal-contenido-payments">

                        </tbody>
                        <tfoot>
                        <tr>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th rowspan="1" colspan="1">Valor a pagar</th>
                            <th rowspan="1" colspan="1">Valor recibido</th>
                            <th rowspan="1" colspan="1">Valor devuelto</th>
                            <th rowspan="1" colspan="1">Fecha</th>
                        </tr>
                        </tfoot>
                    </table>
                    <div id="devoluciones" style="display: none">
                        <div id="keys_spinn" class="text-center" style="margin: 50px 10px; display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>
                        <div id="message_box" class="display: none;"></div>
                        <form role="form" id="devolucion-form" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="txtDescription">Descripción</label>
                                <textarea id="txtDescription" name="txtDescription" class="form-control" rows="3" placeholder="Describa brevemente el caso ..."></textarea>
                                <input type="hidden" id="txttransaction_id">
                            </div>
                            <div class="form-group">
                                <label for="fuComprobante">Adjunte un comprobante</label>
                                <input type="file" id="fuComprobante" name="fuComprobante">

                                <p class="help-block">El archivo debe ser una imagen.</p>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </form>
                    </div>
                    <div id="reprocesos" style="display: none">
                        <div id="reprocesar-info">
                            <p><b>Servicio  :</b> <span id="service_description"></span></p>
                            <p><b>Monto:</b>      <span id="transaction_amount"></span></p>
                            <p><b>Referencia:</b> <span id="transaction_referece"></span></p>
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
    <!-- Print Section -->
    <div id="printSection" class="printSection" style="visibility:hidden;"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <form action="{{route('reporting.cuotas_alquiler.search')}}" method="GET" id="estadoContable-form">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            @if( !\Sentinel::getUser()->inRole('mini_terminal'))
                            <div class="col-md-8">
                                <!-- /.form-group -->
                                <div class="form-group">
                                    {!! Form::label('group', 'Grupo') !!}
                                    {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id','class' => 'form-control select2']) !!}
                                    <br><br>
                                    {!! Form::label('atm', 'Atm') !!}
                                    {!! Form::select('atm_id', $atms, $atm_id, ['id' => 'atm_id','class' => 'form-control select2']) !!}
                                    <br><br>   
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                    </div>
                                </div>
                                <!-- /.form-group -->
                                
                            </div>
                            @else
                            <!-- /.form-group -->
                            <div class="form-group">
                                <br>  
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                </div>
                            </div>
                            <!-- /.form-group -->
                            @endif
                            <!-- /.col -->
                            <div class="col-md-6">
                                <div class="row">
                                   
                                    {{--<div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                    </div>--}}
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer" style="display: block; ">
                        <div class="input-group" style="width:150px; float: right">
                            {!! Form::model(Request::only(['context']),['route' => 'reporting.boletas_depositos.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                            {{--{!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Número de Boleta', 'autocomplete' => 'off' ]) !!}--}}
                            {!! Form::close()!!}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(isset($transactions))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Resultados</h3>
                        <div class="box-tools">
                            <div class="box-footer" style="display: block; ">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped" role="grid">
                                    <tbody>
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Numero de Cuota</th>
                                        <th>Importe</th>
                                        <th>Estado</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Numero de Factura</th>
                                        <th>Acción</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr data-id="{{ $transaction->id }}">
                                            <td>{{ $transaction->id }}</td>
                                            <td>{{ $transaction->nombre }}</td>
                                            <td>{{ $transaction->num_cuota }}</td>
                                            <td>{{ number_format($transaction->importe, 0) }}</td>
                                            <td>
                                                @if($transaction->saldo_cuota != 0)
                                                    @if (Sentinel::hasAccess('reprocesar_alquiler') && is_null($transaction->num_venta) && $transaction->fecha_vencimiento < date('Y-m-d 23:59:59'))
                                                        <span class="label label-danger reprocesar" style="cursor:pointer">Reprocesar</span>
                                                    @else
                                                        <span class="label label-warning">Pendiente</span>
                                                    @endif
                                                @else
                                                    <span class="label label-success">Pagado</span>
                                                @endif
                                            </td>
                                            <td>{{ Carbon\Carbon::parse($transaction->fecha_vencimiento)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $transaction->num_venta }}</td>
                                            <td>
                                                @if (!empty($transaction->num_venta) && !$transaction->reprinted)
                                                    <a class="btn btn-success btn-sm" id="factura"  title="Descargar Factura" href="{{route('reporting.cuotas_alquiler.factura',$transaction->id)}}">
                                                        <span class="fa fa-file-excel-o"></span> &nbsp; Exportar
                                                    </a>
                                                @else
                                                    <a class="btn btn-success btn-sm"  title="Descargar Factura" disabled="disabled">
                                                        <span class="fa fa-file-excel-o" disabled="disabled"></span> &nbsp; Exportar
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">{{ $transactions->total() }} registros en total</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $transactions->appends(['atm_id' => $atm_id, 'group_id' => $group_id])->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

@section('js')
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
    <script>
        $(function(){

            //Cascading dropdown list de redes / sucursales
            $('.select2').select2();
            $('.mostrar').hide();

            //Datemask dd/mm/yyyy
            $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
            //Datemask2 mm/dd/yyyy
            $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
            //reservation date preset
            $('#reservationtime').val()
            if($('#reservationtime').val() == '' || $('#reservationtime').val() == 0){
                var date = new Date();
                var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

                var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
                var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

                $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
            }

            $('.reprocesar').click(function(e){
                e.preventDefault();
                var row = $(this).parents('tr');
                var id = row.data('id');
                swal({
                    title: "Atención!",
                    text: "Está a punto de migrar el registro, está seguro?.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#449d44",
                    confirmButtonText: "Si, migrar!",
                    cancelButtonText: "No, cancelar!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        var url = '/reporting/cuotas_alquiler/insert';
                        var type = "";
                        var title = "";
                        $.post(url,{_token: token,_id: id}, function(result){
                            if(result.error == false){
                                type = "success";
                                title = "Operación realizada!";
                                
                            }else{
                                type = "error";
                                title =  "No se pudo realizar la operación"
                            }
                            //swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                            location.reload();
                            
                        }).fail(function (){
                            swal('No se pudo realizar la petición.');
                        });
                    }
                });
            });

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

            $(document).on('change', '.activar_resumen', function(){
                var isActive = $(this).prop('checked');
                $('#reservationtime').attr('disabled', isActive);
            });

            $('.activar_resumen').trigger('change');

            document.getElementById("factura").onclick = function() {
                var factura = document.getElementById("factura");
                factura.setAttribute("disabled", true);
            };

            $('#group_id').on('change', function(e){
                var group_id = e.target.value;            
                $.get('{{ url('reports') }}/ddl/atms/' + group_id, function(atms) {
                    $('#atm_id').empty();
                    $.each(atms, function(i,item){
                        $('#atm_id').append($('<option>', {
                            value: i,
                            text : item
                        }));
                    });
                });
            });
        });
    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
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

        .resumen {
            margin-top: 7px;
            margin-bottom: -28px;
        }

    </style>
@endsection