
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
                    </div>
                </div>
                <form action="{{ route('reports.mini_retiro.search') }}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-6">
                                 <div class="form-group" id="div">
                                    {!! Form::label('status', 'Estado') !!}
                                    {!! Form::select('status_id',['todos' => 'Todos','pendiente'=>'Pendiente','procesado'=>'Procesado','cancelado' =>'Cancelado','error'=>'Error'],$status_id ,['class' => 'form-control select2','id' => 'status_id']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('tipo', 'Tipo Transaccion') !!}
                                    {!! Form::select('tipo_id', $tipo, $tipo_id, ['id' => 'tipo_id', 'class' => 'form-control select2']) !!}
                                </div>
                                

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rango de Tiempo & Fecha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input name="reservationtime" type="text" id="reservationtime"
                                            class="form-control pull-right" value="{{ $reservationtime}}" />
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label('sucursal', 'Sucursal') !!}
                                        {!! Form::select('atm_id', $atm, $atm_id, ['id' => 'atm_id', 'class' => 'form-control select2']) !!}
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-primary" name="search"
                                            value="search">BUSCAR</button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-success" name="search"
                                            value="download">EXPORTAR</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    
    <div class="box box"  style="display: block;">
        <div class="box-header with-border">
            <h3 class="box-title">Resumen de totales:</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon"><i class="fa fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cantidad de Transacciones</span>
                            <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">{{ $transactionsCount}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto total de Transacciones</span>
                            <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions">{{ $amountView }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Listado de Transacciones</h3>
        </div>
        <div class="box-body  no-padding">
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                        <thead>
                            <tr>
                                <th style="width:10px; background: #d2d6de;">#</th>
                                <th style=" background: #d2d6de;">Nombre ATM</th>
                                <th style=" background: #d2d6de;">Servicio</th>
                                <th style=" background: #d2d6de;">Tipo Transaccion</th>
                                <th style=" background: #d2d6de;">Estado</th>
                                <th style=" background: #d2d6de;">Monto</th>
                                <th style=" background: #d2d6de;">Fecha</th>
                                <th style=" background: #d2d6de;">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($minis as $item)
                                <tr>
                                    @if($item['tipo'] == 'CashOut')
                                    <td>{{ $item['hash_table'] }}</td>
                                    @else
                                    <td>{{ $item['id_transaction'] }}</td>
                                    @endif
                                    <td>{{ $item['atmName'] }}</td>
                                    <td>{{ $item['service'] }}</td>
                                    <td style="font-weight: bold;">
                                         @if($item['tipo'] == 'Vuelto')
                                    <a class="label label-warning">
                                        {{ $item['tipo'] }}
                                     </a>
                                   @endif
                                   @if($item['tipo'] == 'CashOut')
                                    <a class="label label-success">
                                        {{ $item['tipo'] }}
                                     </a>
                                   @endif
                                   @if($item['tipo'] == 'Devolucion')
                                   <a class="label label-danger">
                                    {{ $item['tipo'] }}
                                    </a>
                                  @endif
                                </td>
                                    <td style="font-weight: bold;">
                                    @if($item['status'] == 'cancelado')
                                    <a class="label label-warning">
                                        {{ $item['status']  }}
                                     </a>
                                   @endif
                                   @if($item['status'] == 'procesado')
                                    <a class="label label-success">
                                       {{ $item['status'] }}
                                     </a>
                                   @endif
                                   @if($item['status'] == 'error')
                                   <a class="label label-danger">
                                     {{$item['status']}}
                                    </a>
                                  @endif
                                  @if($item['status'] == 'pendiente')
                                   <a class="label label-default">
                                      {{ $item['status'] }}
                                    </a>
                                  @endif
                                </td>
                                <td style="font-weight: bold;">{{ number_format($item['amount']) }}</td>
                                    <td>{{ Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i:s')  }}</td>
                                    <td>@if(!is_null($item['id_transaction']) && $item['status'] == 'procesado' )  <a class="btn btn-primary btn-flat btn-row btn-relanzar" title="Ver Detalle" onclick="modalView('{{ $item['id_transaction'] }}')" ><i class="fa fa-server"></i></a>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    

    <div id="modal_detalle_mini" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false"
            href="#">
            <div class="modal-dialog modal-dialog-centered" role="document"
                style="background: white; border-radius: 5px; width: 99%;">
                <div class="modal-content" style="border-radius: 10px;">
                    <div class="modal-header">
                        <div class="modal-title" style="font-size: 20px; text-align: center">
                            Detalle de la Transacción
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover dataTable" role="grid"
                                    id="datatable_miniCashOut">
                                    <thead>
                                            <tr>
                                            <th style="text-align:center;background: #d2d6de;">#</th>
                                            <th style="text-align:center;background: #d2d6de;">Servicio</th>
                                            <th style="text-align:center;background: #d2d6de;">Estado</th>
                                            <th style="text-align:center;background: #d2d6de;">Monto de transaccional</th>
                                            <th id="amountRecibidoTH" style="text-align:center; background: #d2d6de;">Monto Recibido</th>
                                            <th id="amountEntregadoTH" style="text-align:center; background: #d2d6de;">Monto Entregado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="text-align:center;">
                                            <td id = "id" ></td>
                                            <td id = "service"></td>
                                            <td id = "status" style="font-size: 20px;"></td>
                                            <td id = "amount" style="font-weight: bold;"></td>
                                            <td id = "amountRecibido" style="font-weight: bold;"></td>
                                            <td id = "amountEntregado" style="font-weight: bold;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="modal-footer" style="text-align: center">
                            <button class="btn btn-danger" onclick="modal_detalle_close()">
                                <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                            </button>

                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
</section>

@section('js')
    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <!-- InputMask -->
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <script>


            function modalView(id){
                $("#modal_detalle_mini").modal();
                var url = '/transactionDataModal';
                    $.post(url,{_token: token,id: id}, function(result){

                        data = result.data;
                        let json = jQuery.parseJSON(data['parameters']);
                        let parameter = jQuery.parseJSON(json);
                       
                        let amountPositivo = Math.abs(data.amount); 
                        let amountFinal    = (data.marca == 'Apostala') ? NumberFormat(parameter.subtraction) :NumberFormat(amountPositivo);
                        let recibido       = NumberFormat(data.valor_recibido);
                        let entregado      = NumberFormat(data.valor_entregado);
                        let status         = labelStatus(data.status);

                        $("#id").text(data.id);
                        $("#service").text(data.marca+' - '+data.servicio);
                        $("#status").html(status);
                        $("#amount").text(amountFinal);
                        $("#amountRecibido").text(recibido);
                        $("#amountEntregado").text(entregado); 

                        $("#amountRecibido").show();
                        $("#amountRecibidoTH").show();
                        $("#amountEntregado").show();
                        $("#amountEntregadoTH").show();

                    if(data.valor_entregado == null){
                        $("#amountRecibido").hide();
                        $("#amountRecibidoTH").hide();
                        $("#amountEntregado").hide();
                        $("#amountEntregadoTH").hide();
                    }
                    
                });

            }

            function labelStatus(data){

                let status = '';

                switch (data) { 
                    case 'iniciated': 
                    status = '<a class="label label-default">Iniciado</a>';
                        break;

                    case 'canceled': 
                    status = '<a class="label label-warning">Cancelado</a>';
                        break;

                    case 'error': 
                    status = '<a class="label label-danger">Error</a>';
                        break;

                    case 'success': 
                    status = '<a class="label label-success">Exitoso</a>';
                        break;
                    default:
                    $status = '';
                }  
                
                return status;


            }

            function NumberFormat(number){
            let amount = new Intl.NumberFormat('es-MX').format(number);
            return amount;
        }

            function modal_detalle_close() {
                 $("#modal_detalle_mini").modal('hide');
             }

        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                //$('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        var table = $('#datatable_1').DataTable(data_table_config);


        $('.mostrar').hide();

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {
            "placeholder": "dd/mm/yyyy"
        });
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {
            "placeholder": "mm/dd/yyyy"
        });
        //reservation date preset
        if ($('#reservationtime').val() == '') {

            var date = new Date();
            var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
            var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

            $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
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
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
            },
            dateLimit: {
                'months': 1,
                'days': -1,

            },
            minDate: new Date(2000, 1 - 1, 1),
            maxDate: new Date(),
            showDropdowns: true,
            locale: {
                applyLabel: 'Aplicar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango Personalizado',
                daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre',
                    'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            },

            format: 'DD/MM/YYYY HH:mm:ss',
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
        });
    </script>


@endsection
