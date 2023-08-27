@extends('app')

@section('title')
    Pago de Cientes
@endsection
@section('content')
    <section class="content-header">
            
        <h1>
            Pago de Cientes
            <small></small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Pago de Cientes</a></li>
        </ol>

        <br/>

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>
        {{--<a href="{{ route('pago_clientes.import') }}" class="btn btn-primary active" role="button">Importar pagos a Clientes</a><p>--}}
    </section>

    <section class="content">
        <!-- Modal -->
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Sucursales del grupo : <label class="grupo"></label></h4>
                    </div>
                    <div class="modal-body">
                        <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                            <thead>
                            <tr role="row">
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Sucursal</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Total Transaccionado</th>
                            </tr>
                            </thead>
                            <tbody id="modal-contenido">

                            </tbody>
                        </table>

                        <div class="text-center" id="cargando" style="margin: 50px 10px"> {{-- clase para bloquear el div y mostrar el loading --}}
                            <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>
        <!-- Print Section -->
        <div id="printSection" class="printSection" style="visibility:hidden;"></div>
        @if(isset($transactions_groups))
            
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <br>
                        <div class="box-tools">
                            <div class="input-group" style="width:200px; float:right; padding-right:10px">
                                {!! Form::model(Request::only(['context']),['route' => 'reporting.resumen_miniterminales.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                                {{--{!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off' ]) !!}--}}
                                {!! Form::close()!!}
                            </div>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Saldos a favor de Clientes resumido hasta ayer</h3>
                        </div>
                        <form action="{{route('pago_clientes.store')}}" method="GET">
                        <!-- /.box-header -->
                        <div class="box-body  no-padding" style="overflow: scroll">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped dataTable" id="datatable_1" role="grid" >
                                        <thead>
                                        <tr>
                                            <th>Grupo</th>
                                            <th>Total Debe</th>
                                            <th>Total Haber</th>
                                            <th>Total Cuotas</th>
                                            <th>Saldo</th>
                                            <th><input class="selectAll" type="checkbox" value="all" name='all' id="selectAll"> Marcar Todo </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($transactions_groups as $transaction)
                                            <tr data-group_id="{{ $transaction->group_id  }}" data-grupo="{{ $transaction->grupo }}">
                                                <td class="{{$transaction->group_id}}">{{ $transaction->grupo }}</td>
                                                <td>{{ number_format($transaction->debito, 0) }}</td>
                                                <td>{{ number_format($transaction->credito, 0) }}</td>
                                                <td>{{ number_format($transaction->cuotas, 0) }}</td>
                                                <td>{{ number_format($transaction->saldo, 0) }}</td>
                                                <td><div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{$transaction->group_id}}" name='group[]' id="flexCheckDefault">
                                                </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-8">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-block btn-success download" name="download" value="download" id="download" disabled>GENERAR TXT</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal ayuda-->
        <div id="modal_help" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                style="width: 800px; background: white; border-radius: 5px">
                <!-- Modal content-->
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="modal-title" style="font-size: 20px;">
                            Ayuda e información &nbsp; <small> <b> </b> </small>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <!-- TAB PANNEL -->
                    <div class="panel with-nav-tabs">
                        <div class="panel-heading">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                        Resumen al dia de hoy </a>
                                </li>
                                <li><a href="#tab_help_1" data-toggle="tab">
                                    Resumen al Cierre </a>
                                </li>
                                <li><a href="#tab_help_2" data-toggle="tab">
                                        Búsqueda personalizada </a>
                                </li>
                            </ul>
                        </div>
                        <div class="panel-body">
                            <div class="tab-content">
                                <!-- Solamente el contenido se cambia -->
                                <div class="tab-pane fade in active" id="tab_help_0">
                                    <h5><b>Definición de Resumen al dia de hoy:</b>
                                        El resumen al dia de hoy muestra un resumen en linea de la deuda del cliente, en dicho resumen 
                                        se tiene en cuenta todas las transacciones, todo lo depositado, hasta el momento de la consulta. </h5>
                                    <hr />

                                    <b>Total Transaccionado:</b> Todas las transacciones del cliente hasta la fecha. <br />

                                    <b>Total Depositado:</b>
                                    Todos los depositos de boletas del cliente hasta la fecha.<br />

                                    <b>Saldo:</b>
                                    El resultado de la diferencia entre lo transaccionado y lo depositado hasta la fecha. <br />
                                </div>
                                <div class="tab-pane fade" id="tab_help_1">
                                    <h5><b>Definición de Resumen al cierre:</b>
                                        El resumen al cierre muestra un resumen segun la regla estipulada para cada cliente, en dicho 
                                        resumen se tiene en cuenta todas las transacciones hasta el ultimo dia de bloqueo adeudado y todo 
                                        lo depositado hasta la fecha.  </h5>
                                    <hr/>

                                    <b>Total Transaccionado:</b> Todas las transacciones del cliente hasta un dia anterior, al ultimo dia de bloqueo adeudado.
                                    <br />
                                    <b>Ejemplo 1: </b>
                                        Un lunes(dia de bloqueo) se habilita el resumen hasta el cierre y el resultado del total transaccionado 
                                        seria todo lo transaccionado hasta el dia antes(domingo) hasta las 23:59:59.
                                    <br />
                                    <b>Ejemplo 2: </b>
                                    Un Martes(NO es dia de bloqueo) se habilita el resumen hasta el cierre y el resultado del total transaccionado 
                                    seria todo lo transaccionado hasta el dia antes del ultimo dia de bloqueo(domingo) hasta las 23:59:59 ya que el 
                                    cierre sigue siendo Lunes.
                                    <br /><br />

                                    <b>Total Depositado:</b>
                                    Todos los depositos de boletas del cliente hasta la fecha. <br />

                                    <br />

                                    <b>Saldo:</b>
                                    El resultado de la diferencia entre lo transaccionado y lo depositado. <br /><br /><br />

                                    <b>Regla:</b>
                                    Las reglas para consultar la transaccion al cierre son:
                                    <br />

                                    <ul>
                                        <li><b> Lunes: </b> Si buscas un dia Lunes se tiene en cuenta todas las transacciones del 
                                            cliente hasta el dia antes(Domingo) a las 23:59:59, ya que es dia de bloqueo.</li>

                                        <li><b> Martes: </b> Si buscas un dia Martes se tiene en cuenta todas las transacciones del 
                                            cliente hasta el dia antes del ultimo dia de bloqueo(Domingo) a las 23:59:59, ya que 
                                            NO es dia de bloqueo.</li>

                                        <li><b> Miercoles: </b> Si buscas un dia Miercoles se tiene en cuenta todas las transacciones 
                                            del cliente hasta el dia antes(Martes) a las 23:59:59, ya que es dia de bloqueo.</li>

                                        <li><b> Jueves: </b> Si buscas un dia Jueves se tiene en cuenta todas las transacciones del 
                                            cliente hasta el dia antes del ultimo dia de bloqueo(Martes) a las 23:59:59, ya que 
                                            NO es dia de bloqueo. </li>

                                        <li><b> Viernes: </b> Si buscas un dia Viernes se tiene en cuenta todas las transacciones 
                                            del cliente hasta el dia antes(Jueves) a las 23:59:59, ya que es dia de bloqueo. </li>

                                        <li><b> Sabado: </b> Si buscas un dia Sabado se tiene en cuenta todas las transacciones del 
                                            cliente hasta el dia antes del ultimo dia de bloqueo(Jueves) a las 23:59:59, ya que 
                                            NO es dia de bloqueo. </li>

                                        <li><b> Domingo: </b> Si buscas un dia Domingo se tiene en cuenta todas las transacciones del 
                                            cliente hasta el dia antes del ultimo dia de bloqueo(Jueves) a las 23:59:59, ya que 
                                            NO es dia de bloqueo. </li>
                                    </ul>
                                </div>
                                <div class="tab-pane fade" id="tab_help_2">
                                    <h5><b>Definición de Búsqueda Personalizada:</b>
                                        La Búsqueda Personalizada muestra un resumen donde se tienen en cuenta todas las transacciones 
                                        segun el rango de tiempo y fecha seleccionada, al igual que los depositos de boletas confirmados. <br /></h5>
                                    <hr />

                                    <b>Total Transaccionado:</b> Todas las transacciones del cliente en el rango de fecha y tiempo seleccionado.<br />

                                    <b>Total Depositado:</b>
                                    Todos los depositos de boletas confirmados en el rango de fecha y tiempo seleccionado. <br />

                                    <b>Saldo:</b>
                                    El resultado de la diferencia entre lo transaccionado y lo depositado en el rango de fecha y tiempo seleccionado. <br />

                                    <br /> <br />
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB PANNEL -->
                    </div>

                    <div class="modal-footer">
                        <div style="float:right">
                            <div class="btn-group mr-2" role="group">
                                <button class="btn btn-danger pull-right" title="Cerrar ayuda e información."
                                    style="margin-right: 10px" data-dismiss="modal">
                                    <span class="fa fa-remove" aria-hidden="true"></span>
                                    &nbsp; Cerrar ayuda
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

        <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
        <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
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

                //Date range picker
                $('#reservation').daterangepicker();

                $('#estadoContable-form').validate({
                    rules: {
                        "user_id": {
                            required: true,
                        },
                    },
                    messages: {
                        "user_id": {
                            required: "Seleccione un usuario",
                        },
                    },
                    errorPlacement: function (error, element) {
                        error.appendTo(element.parent());
                    }
                });

                var table = $('#datatable_1').DataTable({
                    "paging":       true,
                    "ordering":     true,
                    "info":         true,
                    "searching":    true,
                    dom: '<"pull-left"f><"pull-right"l>tip',
                    language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    },
                    columnDefs: [{
                        targets: 'no-sort',
                        orderable: false,
                    }],
                });

                var ths = $("#datatable_1").find("th");

                $("#selectAll").click(function(){
                    $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
                });

                $("input[type=checkbox]").change(function(){
                    var array = []
                    var checkboxes = document.querySelectorAll('input[id=flexCheckDefault]:checked')

                    console.log(Array.from(document.querySelectorAll("input[id=flexCheckDefault]:checked")).map(e => e.value));
                    if(checkboxes.length === 0){
                        $('#download').attr('disabled', true);
                    }else{
                        $('#download').attr('disabled', false);
                    }
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
@endsection