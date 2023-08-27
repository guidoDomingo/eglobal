<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <form action="{{route('reports.conciliations_details.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('atm', 'ATMs') !!}
                                    {!! Form::select('atm_id', $atms, $atm_id , ['id' => 'atm_id','class' => 'form-control select2']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('status', 'Estados') !!}
                                    {!! Form::select('status_id', $status, $status_id, ['id' => 'status_id','class' => 'form-control select2']) !!}
                                </div>
                             
                                <div class="form-group">
                                    <label>Tipo de transacción</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                </div>
                                <div class="col-md mostrar">
                                    <div class="form-group">
                                        <label>Serivicio</label>
                                        {!! Form::select('service_request_id', [0], $service_request_id, ['class' => 'form-control select2', 'id' => 'servicioRequestId']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rango de Tiempo & Fecha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{old('reservationtime', $reservationtime ?? '')}}" />
                                    </div>
                                </div>
                                <br>
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
                    </div>
                    <div class="box-tools">
                        <div class="input-group" style="width:200px; float:right; padding-right:10px">
                            {!! Form::model(Request::only(['context']),['route' => 'reports.conciliations_details.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                            {!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nro. Ingreso', 'autocomplete' => 'off' ]) !!}
                            {!! Form::close()!!}
                        </div>
                    </div>
                    <div class="box-footer" style="display: block;">

                    </div>
                    
                </form>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">A ingresar - Facturas PE global</h3>
        </div>
        <div class="box-body  no-padding">
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Id transaccion</th>
                                <th>Numero Factura</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generics as $item)
                         <tr>
                            <td>{{$item->id}}</td>
                            <td>{{$item->transaction_id}}</td>
                            <td>{{$item->invoice_number}}</td>
                            <td>
                                @if($item->status_code == '-28')
                                 <a class="label label-warning">
                                    Codigo vendedor no asignado
                                  </a>
                                @endif
                                @if($item->status_code == '212')
                                 <a class="label label-success">
                                    Error general ondanet
                                  </a>
                                @endif
                                @if($item->status_code == '1')
                                 <a class="label label-info">
                                    error no registrado
                                  </a>
                                @endif
                            </td>
                            <td>{{$item->client_id}}</td>
                            <td>{{ date("Y-m-d H:i:s", strtotime($item->created_at)) }}</td>
                            <td>{{number_format($item->amount,0)}}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
     </div>

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">A ingresar - Facturas PE por cajero</h3>
            <div class="box-tools">
                <div class="input-group" style="width:150px;">
                    {!! Form::model(Request::only(['name']),['route' => 'applications.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                    {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'ATM', 'autocomplete' => 'off' ]) !!}
                    {!! Form::close()!!}
                </div>
            </div>
        </div>

        <div class="box-body  no-padding">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tbody><thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Atm</th>
                            <th>ID Transacción</th>
                            <th>Servicio</th>
                            <th>Monto</th>
                            <th>Mensaje</th>
                            <th style="width:150px">Creado</th>
                            <th style="width:150px">Modificado</th>
                            <th style="width:100px">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invoices as $invoice)
                        <tr data-id="1">
                            <td>{{$invoice->id}}</td>
                            <td>{{$invoice->atm_code}}</td>
                            <td>{{$invoice->transaction_id}}</td>
                            <td>{{$invoice->service_description}}</td>
                            <td>{{number_format($invoice->amount,0)}}</td>
                            <td>{{$invoice->response}}</td>
                            <td>{{$invoice->created_at}}</td>
                            <td>{{$invoice->updated_at}}</td>
                           <td></td>
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
                    <div class="dataTables_info" role="status" aria-live="polite"> {{count($invoices)}} registros en total</div>
                </div>
                <div class="col-sm-7">
                    <div class="dataTables_paginate paging_simple_numbers">

                    </div>
                </div>
            </div>
        </div>
    </div>
   


    @if(isset($incomes))
        <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">INGRESOS / Conciliaciones por cajero</h3>
                        <a class="btn btn-secundary btn-flat btn-row" onclick="relanzar_todos('{{ $incomes_error }}')" style="width:130px; float:right; padding-right:10px" title="Relanzar Todos" >Relanzar Todos &nbsp;<i class="fa fa-rotate-left"></i></a>
                </div>
                <!-- /.box-header -->
                <div class="box-body  no-padding">
                    <div class="row">
                        <div class="col-xs-12">
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Atm</th>
                                    <th>ID Transacción</th>
                                    <th>Servicio</th>
                                    <th>Monto</th>
                                    <th>Mensaje</th>
                                    <th style="width:150px">Creado</th>
                                    <th style="width:150px">Modificado</th>
                                    <th>Estado</th>
                                    <th style="width:100px">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($incomes as $income)
                                <tr data-id="{{$income->id}}">
                                    <td>{{$income->id}}</td>
                                    <td>{{$income->atm_code}} - {{$income->name}}</td>
                                    <td>{{$income->transaction_id}}</td>
                                    <td>{{$income->service_description}}</td>
                                    <td>{{number_format($income->amount,0)}}</td>
                                    <td>{{$income->response}}</td>
                                    <td>{{$income->created_at}}</td>
                                    <td>{{$income->updated_at}}</td>
                                    @if ($income->destination_operation_id == "0")
                                        <td>Pendiente</td>
                                    @elseif ($income->destination_operation_id == "1")
                                        <td>Error</td>
                                    @elseif ($income->destination_operation_id > "1")
                                        <td>Exitoso</td>
                                    @endif

                                    <td style="text-align: center">
                                        @if ($income->destination_operation_id == "1")
                                            <a class="btn btn-success btn-flat btn-row btn-relanzar" title="Relanzar"  ><i class="fa fa-rotate-left"></i></a>
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
                            <div class="dataTables_info" role="status" aria-live="polite">{{count($incomes)}} registros en total</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {!! $incomes->appends(['status_id' => $status_id ,'reservationtime' => $reservationtime ])->render() !!}

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
  <!-- datatables -->
  <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
  <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
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
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script>


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
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //reservation date preset
        if($('#reservationtime').val() == ''){

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
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            dateLimit: {
                    'months': 1,
                    'days': -1,

                }, 
                minDate: new Date(2000, 1 - 1, 1),
                maxDate:new Date(), 
                showDropdowns:true,
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



        $('.btn-relanzar').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            swal({
                title: "Atención!",
                text: "Está a punto de relanzar el registro, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#449d44",
                confirmButtonText: "Si, Relanzar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var url = '/reports/conciliations_details/relaunch_transaction';
                    var type = "";
                    var title = "";
                
                    $.post(url,{_token: token,_id: id}, function(result){
                        //console.log(result);
                    if(result.error == false){
                        type = "error";
                        title = "No se pudo realizar la operación";
                            
                    }else{
                        type = "success";
                        title =  "Operación realizada!";
                    }
                        swal({   
                            title: title,   
                            text: result.message,   
                            type: type,   
                            confirmButtonText: "Aceptar" });
                        location.reload();

                    }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
                }

            });
        });

        function relanzar_todos(incomes_error) {
            var ids = incomes_error;
            swal({
                title: "Atención!",
                text: "Está a punto de relanzar todas las conciliaciones con estado de error, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#449d44",
                confirmButtonText: "Si, Relanzar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var url = '/reports/conciliations_details/relaunch_transaction_all';
                    var type = "";
                    var title = "";
                
                    $.post(url,{_token: token,_ids: ids}, function(result){
                        //console.log(result);
                        if(result.error == false){
                            type = "error";
                            title = "No se pudo realizar la operación";    
                        }else{
                            type = "success";
                            title =  "Operación realizada!";
                        }
                        swal({   
                            title: title,   
                            text: result.message,   
                            type: type,   
                            confirmButtonText: "Aceptar" 
                        });
                        
                        location.reload();
                    }).fail(function (){
                        swal('No se pudo realizar la petición.');
                    });
                }
            });
        }


        
        $(document).on('change','#serviceId',function(){
            var valor = this.value;
            var urlGetServices = "{{ route('reports.get_service_request') }}";

            if(valor.search('-') != -1){
                $.get(urlGetServices, {id: valor}).done(function(data){
                    $('.mostrar').show();
                    $('#servicioRequestId').empty().trigger('change');
                    $('#servicioRequestId').select2({data: data});
                    if(servicioSeleccionado != ''){
                        $('#servicioRequestId').val(servicioSeleccionado).trigger('change');
                    }
                });
            }else{
                $('#servicioRequestId').select2('data', null);
                $('.mostrar').hide();
            }
        });

        $('#serviceId').trigger('change');
    </script>
@endsection

