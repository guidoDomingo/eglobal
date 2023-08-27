<section class="content">
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="display:none;" class="modal-title" id="titulo_transactions">Sucursales del grupo : <label class="grupo"></label></h4>
                    <h4 style="display:none;" class="modal-title" id="titulo_reversions">Reversiones del grupo : <label class="grupo"></label></h4>
                    <h4 style="display:none;" class="modal-title" id="titulo_cashouts">Cashouts del grupo: <label class="grupo"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1" id="titulo2_transactions">Sucursal</th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1" id="titulo2_reversions">Marca</th>
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
                <form action="{{route('reporting.resumen_detallado_miniterminal.search')}}" method="GET" id="estadoContable-form">
                    @if ( !\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal'))

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
                                        <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{old('reservationtime', $reservationtime ?? '')}}" />
                                    </div>
                                    <div class="resumen">
                                        <label>
                                            Resumen al dia de hoy
                                        </label>
                                        <label class="switch">
                                            <input type="checkbox" class="activar_resumen" name="activar_resumen" @if(isset($reservationtime) && $reservationtime == 0) checked="checked" @endif>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <!-- /.input group -->
                                </div>
                                <!-- /.form group -->
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
                        <!-- /.row -->
                    </div>
                    <div class="box-tools">
                        <div class="input-group" style="width:200px; float:right; padding-right:10px">
                            {!! Form::model(Request::only(['context']),['route' => 'reporting.resumen_detallado_miniterminal.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                            {!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off' ]) !!}
                            {!! Form::close()!!}
                        </div>
                    </div>
                @elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal'))
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
                                        <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{old('reservationtime', $reservationtime ?? '')}}" />
                                    </div>
                                    <div class="resumen">
                                        <label>
                                            Resumen al dia de hoy
                                        </label>
                                        <label class="switch">
                                            <input type="checkbox" class="activar_resumen" name="activar_resumen" @if(isset($reservationtime) && $reservationtime == 0) checked="checked" @endif>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <!-- /.input group -->
                                </div>
                                <!-- /.form group -->
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
                        <!-- /.row -->
                    </div>
                @endif

                <!-- /.box-body -->
                <div class="box-footer" style="display: block;">
                </div>
                </form>
            </div>
        </div>
    </div>
    @if(isset($transactions_groups))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Resultados</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped" role="grid">
                                    <tbody>
                                    <thead>
                                    <tr>
                                        <th>Grupo</th>
                                        <th>Total Transaccionado</th>
                                        <th>Total Paquetigo</th>
                                        <th>Total Personal</th>
                                        <th>Total Claro</th>
                                        <th>Total Pago Cashout</th>
                                        <th>Total Pagado</th>
                                        <th>Total Reversado</th>
                                        <th>Total Cashout</th>
                                        <th>Saldo</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions_groups as $transaction)
                                        <tr data-group_id="{{ $transaction->group_id  }}" data-grupo="{{ $transaction->grupo }}">
                                            <td class="{{$transaction->group_id}}">
                                                {{ $transaction->grupo }}
                                                <div class="btn-group">
                                                    <buttom class="btn btn-default btn-xs" title="Mostrar atms">
                                                        <i class="pay-info fa fa-info-circle" style="cursor:pointer"></i>
                                                    </buttom>
                                                </div>
                                            </td>
                                            <td>{{ number_format($transaction->transacciones, 0) }}</td>
                                            <td>{{ number_format($transaction->paquetigos, 0) }}</td>
                                            <td>{{ number_format($transaction->personal, 0) }}</td>
                                            <td>{{ number_format($transaction->claro, 0) }}</td>
                                            <td>{{ number_format($transaction->pago_cash, 0) }}</td>
                                            <td>{{ number_format($transaction->depositos, 0) }}</td>
                                            <td>
                                                {{ number_format($transaction->reversiones, 0) }}
                                                <div class="btn-group">
                                                    <buttom class="btn btn-default btn-xs" title="Detalles de Reversiones">
                                                        <i class="info-reversiones fa fa-info-circle" style="cursor:pointer"></i>
                                                    </buttom>
                                                </div>
                                            </td>
                                            <td>
                                                {{ number_format($transaction->cashouts, 0) }}
                                                <div class="btn-group">
                                                    <buttom class="btn btn-default btn-xs" title="Detalles de Cashouts">
                                                        <i class="info-cashouts fa fa-info-circle" style="cursor:pointer"></i>
                                                    </buttom>
                                                </div>
                                            </td>
                                            <td>{{ number_format($transaction->saldo, 0) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_debe_grupo }}</h2>
                                            <span class="description-text">TOTAL TRANSACCIONES</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_paquetigo_grupo }}</h2>
                                            <span class="description-text">TOTAL PAQUETIGO</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_personal_grupo }}</h2>
                                            <span class="description-text">TOTAL PERSONAL</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_claro_grupo }}</h2>
                                            <span class="description-text">TOTAL CLARO</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_pago_cash_grupo }}</h2>
                                            <span class="description-text">TOTAL PAGO CASHOUT</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_haber_grupo }}</h2>
                                            <span class="description-text">TOTAL PAGADO</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_reversion_grupo }}</h2>
                                            <span class="description-text">TOTAL REVERSADO</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-3">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_cashout_grupo }}</h2>
                                            <span class="description-text">TOTAL CASHOUT</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xs-12">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ $total_saldo_groups }}</h2>
                                            <span class="description-text">SALDO</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">{{ $transactions_groups->total() }} registros en total</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $transactions_groups->appends(['reservationtime' => $reservationtime])->render() !!}
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
            //$('#reservationtime').val()
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

            $('.pay-info').on('click',function(e){
                $('#titulo_transactions').show();
                $('#titulo2_transactions').show();
                $("#titulo_reversions").hide();
                $("#titulo_cashouts").hide();
                $("#titulo2_reversions").hide();

                e.preventDefault();
                var row = $(this).parents('tr');
                var group_id = row.data('group_id');
                console.log(group_id);
                var grupo = row.data('grupo');

                if(moment(valuee, 'DD/MM/YYYY hh:mm:ss - DD/MM/YYYY hh:mm:ss').isValid()){
                    var fecha=valuee.split("-");
                    var dayinit=moment(fecha[0], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var dayend=moment(fecha[1], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var day=dayinit + '-' + dayend;
                }else{
                    day=0;
                }
                $("#modal-contenido").html('');
                $('#cargando').show();

                $.get('{{ url('reports') }}/info/get_branch_groups/' + group_id + '/' + day, 
            
                    function(data) {
                        $(".grupo").html(grupo);
                        $("#modal-contenido").html(data);
                        $("#detalles").show();
                        $('#cargando').hide();
                        $("#myModal").modal('show');
                    });
                $("#myModal").modal('show');

            });

            $('.info-reversiones').on('click',function(e){
                $('#titulo_transactions').hide();
                $('#titulo2_transactions').hide();
                $('#titulo_cashouts').hide();
                $("#titulo_reversions").show();
                $("#titulo2_reversions").show();

                e.preventDefault();
                var row = $(this).parents('tr');
                var group_id = row.data('group_id');
                console.log(group_id);
                var grupo = row.data('grupo');

                if(moment(valuee, 'DD/MM/YYYY hh:mm:ss - DD/MM/YYYY hh:mm:ss').isValid()){
                    var fecha=valuee.split("-");
                    var dayinit=moment(fecha[0], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var dayend=moment(fecha[1], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var day=dayinit + '-' + dayend;
                }else{
                    day=0;
                }
                $("#modal-contenido").html('');
                $('#cargando').show();

                $.get('{{ url('reports') }}/info/get_reversions_groups/' + group_id + '/' + day, 
            
                function(data) {
                    $(".grupo").html(grupo);
                    $("#modal-contenido").html(data);
                    $("#detalles").show();
                    $('#cargando').hide();
                    $("#myModal").modal('show');
                });
                $("#myModal").modal('show');

            });

            $('.info-cashouts').on('click',function(e){
                $('#titulo_transactions').hide();
                $('#titulo2_transactions').hide();
                $('#titulo_cashouts').show();
                $("#titulo_reversions").hide();
                $("#titulo2_reversions").show();

                e.preventDefault();
                var row = $(this).parents('tr');
                var group_id = row.data('group_id');
                console.log(group_id);
                var grupo = row.data('grupo');

                if(moment(valuee, 'DD/MM/YYYY hh:mm:ss - DD/MM/YYYY hh:mm:ss').isValid()){
                    var fecha=valuee.split("-");
                    var dayinit=moment(fecha[0], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var dayend=moment(fecha[1], 'DD/MM/YYYY hh:mm:ss').valueOf();
                    var day=dayinit + '-' + dayend;
                }else{
                    day=0;
                }
                $("#modal-contenido").html('');
                $('#cargando').show();

                $.get('{{ url('reports') }}/info/get_cashouts_groups/' + group_id + '/' + day, 
            
                function(data) {
                    $(".grupo").html(grupo);
                    $("#modal-contenido").html(data);
                    $("#detalles").show();
                    $('#cargando').hide();
                    $("#myModal").modal('show');
                });
                $("#myModal").modal('show');

            });

            $(document).on('change', '.activar_resumen', function(){
                var isActive = $(this).prop('checked');
                $('#reservationtime').attr('disabled', isActive);
            });

            $('.activar_resumen').trigger('change');
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