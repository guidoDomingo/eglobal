@extends('layout')
@section('title')
    Baja de miniterminales
@endsection
@section('refresh')
    <meta http-equiv="refresh" content="900">
@endsection
@section('content')
    <section class="content-header">
    
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li class="active">ATMS</li>
        </ol>
    </section>

    <br />


    <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
        <div>
            <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
    </div>

    <section class="content" id="content" style="display: none">
        @include('partials._flashes')

        {{-- <div class="callout callout-info" style="height: 120px; width: 100%;">
            <div class="form-group" style="position: relative;">
                <div style="display: inline-block">
                    <h4 style="text-align: center"><i class="fa fa-info"></i> GRUPO:</h4>
                    <h5>Ruc: <b>{{$grupo->ruc}}</b></h5>
                    <h5>Grupo: <b>{{$grupo->description}}</b></h5>      
                </div>
                <div style="display: inline-block; margin-left:40%;">
                    <a class="btn btn-bitbucket btn-app-conf" title="Administrar grupo" onclick="view_options({{ $grupo->id }})"><i class="fa fa-cogs"></i><br><small>Administrar</small></a>
                    <a class="btn btn-primary btn-app-conf" title="Volver" href="{{ route('atms.baja') }}"><i class="fa fa-cogs"></i><br><small>Volver</small></a>
                </div>
            </div>
        </div> --}}
        <div class="callout callout-info" style="height: 140px;">
            <div class="form-row">   
                
                <div class="form-group col-md-9">                
                    <h4 style="text-align: center"><i class="fa fa-info"></i> CLIENTE:</h4>
                    <div class="form-group col-md-8">
                        <h5>Ruc: <b>{{$grupo[0]['ruc']}}</b></h5>
                        <h5>Cliente: <b>{{$grupo[0]['description']}}</b></h5>  
                        @if ($grupo[0]['status'] == 0)
                            <h5>Estado del cliente: <b><span class="bg-green">&nbsp; ACTIVO &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 1)
                            <h5>Estado del cliente: <b><span class="bg-gray">&nbsp; BLOQUEADO &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 2)
                            <h5>Estado del cliente: <b><span class="bg-yellow">&nbsp; EN PROCESO DE INACTIVACIÓN &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 3)
                            <h5>Estado del cliente: <b><span class="bg-blue">&nbsp; GESTIÓN COMERCIAL &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 4)
                            <h5>Estado del cliente: <b><span class="bg-blue">&nbsp; GESTIÓN PREJUDICIAL &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 5)
                            <h5>Estado del cliente: <b><span class="bg-blue">&nbsp; GESTIÓN JUDICIAL &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 6)
                            <h5>Estado del cliente: <b><span class="bg-blue">&nbsp; GESTIÓN ASEGURADORA &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 7)
                            <h5>Estado del cliente: <b><span class="bg-red">&nbsp; INACTIVO &nbsp;</span></b></h5>  
                        @elseif ($grupo[0]['status'] == 8)
                            <h5>Estado del cliente: <b><span class="bg-purple">&nbsp; LOGÍSTICA &nbsp;</span></b></h5>  
                        @else
                            <h5>Estado del cliente: <b><span class="bg-red">&nbsp; DESCONOCIDO &nbsp;</span></b></h5>  
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <h5>Deuda Multa Total: <b><span class="bg-gray">&nbsp;{{number_format($grupo[0]['total'])}} Gs.&nbsp;</span></b></h5>
                        <h5>Saldo: <b><span class="bg-gray">&nbsp;{{number_format($saldo_cliente)}} Gs.&nbsp;</span></b></h5> 
                    </div>

                </div>  
                
                <div class="form-group col-md-3" >
                    <div class="btn-group btn-group-justified" style="margin-top: 5%;">
                        <a class="btn btn-bitbucket" title="Administrar grupo" onclick="view_options({{ $grupo[0]['id'] }})"><i class="fa fa-cogs"></i><br><small>Administrar</small></a>
                        <a style="background-color: #F4F4F4; color: rgb(0, 0, 0); border-color:#ddd;" class="btn" title="Volver" href="{{ route('atms.baja') }}"><i class="fa fa-cogs"></i><br><small>Volver</small></a>
                    </div>
                </div>
                       
            </div>

        </div>


        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Listado de ATMs</h4>
                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1" style="font-size: 13px">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th style="text-align:center; vertical-align: middle;">Red</th>
                                    <th style="text-align:center; vertical-align: middle;">ATM</th>
                                    <th style="text-align:center; vertical-align: middle;">Código identificador</th>
                                    <th style="text-align:center; vertical-align: middle;">Housing</th>
                                    <th style="text-align:center; vertical-align: middle;">Estado del ATM</th>

                                    {{-- <th style="text-align:center; vertical-align: middle;">Seleccionar</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($atms as $atm)
                                    <td style="text-align:center; vertical-align: middle;">{{ $atm->atm_id }}</td>
                                    <td style="text-align:center; vertical-align: middle;">{{ $atm->atm_owner }}</td>
                                    <td style="text-align:center; vertical-align: middle;">{{ $atm->atm_name }}</td>
                                    <td style="text-align:center; vertical-align: middle;">{{ $atm->atm_code }}</td>
                                    @if ( $atm->serialnumber != null)
                                        <td style="text-align:center; vertical-align: middle;">{{ $atm->serialnumber }}</td>
                                    @else
                                        <td style="text-align:center; vertical-align: middle;"> SIN SERIAL</td>
                                    @endif
                                    @if ($atm->activo != null)
                                        <td style="text-align:center; vertical-align: middle;"> INACTIVO</td>
                                    @else
                                        <td style="text-align:center; vertical-align: middle;"> ACTIVO</td>
                                    @endif

                                    {{-- <td style="text-align:center; vertical-align: middle;">
                                        <label >
                                            <input type="checkbox" class="minimal-red" checked>
                                        </label>
                                    </td> --}}
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal comercial-->
    <div id="modal_comercial" class="modal modal-large fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        <b>ACCIONES</b>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="btn-group" role="group" style="display: grid">
                    
                        <div class="box">
                            <div class="box-header" style="font-size: 20px; text-align: center">
                                <h3 class="box-title"><b>GESTIÓN DE COBRANZAS</b></h3>
                                <div class="modal-title" style="font-size: 15px; text-align: center">
                                    ELABORACIÓN DE DOCUMENTOS
                                </div>                            
                            </div>
                            <div class="box-body" style="text-align: center">
                                @if (Sentinel::hasAccess('atms.group.pagare'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Pagares" id="pagare_cobranzas" ><i class="fa fa-file-text-o"></i><br><small>Pagaré</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.rescision'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Nota de Rescision" id="rescision" ><i class="fa fa-file-text-o"></i><br><small>Nota de rescisión</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.nota.retiro'))
                                {{-- <a class="btn btn-bitbucket btn-app-acciones" title="Nota de retiro" id="retiro" ><i class="fa fa-file-text-o"></i><br><small>Nota de retiro</small></a>  --}}
                                <a class="btn btn-bitbucket btn-app-acciones" title="Generar Nota de retiro" id="generar_nota_retiro" ><i class="fa fa-file-word-o"></i><br><small>Nota de retiro</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.params'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Cambiar de estado" id="cambio_estado" ><i class="fa fa-retweet"></i><br><small>Actualizar estado</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.penalizacion'))
                                <a class="btn btn-warning btn-app-acciones" title="Generar factura" id="generar_factura" ><i class="fa fa-file-text-o"></i><br><small>Generar Factura</small></a> 
                                @endif
                                {{-- @if (Sentinel::hasAccess('atms.params'))
                                <a class="btn btn-success btn-app-acciones" title="Cobranzas" id="conbranzas" ><i class="fa fa-money"></i><br><small>Cobranzas</small></a>
                                @endif --}}
                            </div>
                        </div>
                        @if (\Sentinel::getUser()->inRole('atms_v2.area_logisticas') || \Sentinel::getUser()->inRole('superuser'))                        
                            <div class="box">
                                <div class="box-header" style="font-size: 20px; text-align: center">
                                    <h3 class="box-title"><b>GESTIÓN DE LOGÍSTICAS</b></h3>
                                    {{-- <div class="modal-title" style="font-size: 15px; text-align: center">
                                        GENERAR NOTA DE RETIRO
                                    </div> --}}
                                </div>
                                <div class="box-body" style="text-align: center">
                                    {{-- @if (Sentinel::hasAccess('atms.group.retiro'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Generar Nota de retiro" id="generar_nota_retiro" ><i class="fa fa-file-word-o"></i><br><small>Nota de retiro</small></a> 
                                    @endif --}}
                                                                                                      
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Retiro de dispositivos" id="retiro_dispositivo" ><i class="fa fa-edit"></i><br><small>Registrar Retiro</small></a> 
                                    
                                </div>
                            </div>
                        @endif

                        <div class="box">
                            <div class="box-header" style="font-size: 20px; text-align: center">
                                <h3 class="box-title"><b> GESTIÓN DE COMPRAS</b></h3>
                            </div>
                            <div class="box-body" style="text-align: center">
                                @if (Sentinel::hasAccess('atms.group.presupuesto'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Presupuesto de reparación" id="presupuesto" ><i class="fa fa-edit"></i> <br><small>Presupuestos</small> </a>
                                @endif
                                @if (Sentinel::hasAccess('atms.change.status.compras'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Cambiar de estado" id="cambio_estado_compras" ><i class="fa fa-retweet"></i><br><small>Actualizar estado </small></a> 
                                @endif
                            </div>
                        </div>

                        <div class="box">
                            <div class="box-header" style="font-size: 20px; text-align: center">
                                <h3 class="box-title"><b> GESTIÓN DE LEGALES</b></h3>
                                <div class="modal-title" style="font-size: 15px; text-align: center">
                                    INTENTO DE COBRO | GESTIÓN COMERCIAL
                                </div>
                            </div>
                            <div class="box-body" style="text-align: center">
                                @if (Sentinel::hasAccess('atms.group.compromiso'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Compromiso de pago" id="compromiso_pago"  ><i class="fa fa-file-text-o"></i><br><small>Compromisos</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.intimacion'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Intimación" id="intimacion" ><i class="fa fa-file-text"></i><br><small>Intimaciones</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.change.status.comercial'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Cambiar de estado" id="cambio_estado_legales" ><i class="fa fa-retweet"></i><br><small>Actualizar estado </small></a> 
                                @endif

                                <br>
                                <br>
                                <div class="modal-title" style="font-size: 15px; text-align: center">
                                    INTENTO DE COBRO | GESTIÓN PRE-JUDICIAL
                                </div>
                                <br>
                                @if (Sentinel::hasAccess('atms.group.pagare'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Pagares" id="pagare_legales" ><i class="fa fa-file-text-o"></i><br><small>Pagaré</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.change.status.prejudicial'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Cambiar de estado" id="cambio_estado_prejudicial" ><i class="fa fa-retweet"></i><br><small>Actualizar estado </small></a> 
                                @endif
                                <br>
                                <br>
                                <div class="modal-title" style="font-size: 15px; text-align: center">
                                    INTENTO DE COBRO | GESTIÓN JUDICIAL
                                </div>
                                <br> 
                                @if (Sentinel::hasAccess('atms.group.remision.pagare'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Remisión de pagaré" id="remision" ><i class="fa fa-file-text-o"></i><br><small>Remisión de pagaré</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.recibo.perdida'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Recibo de pérdida" id="perdida" ><i class="fa fa-file-text-o"></i><br><small>Recibo de pérdida</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.recibo.ganancia'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Recibo de ganancia" id="ganancia" ><i class="fa fa-file-text-o"></i><br><small>Recibo de ganancia</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.group.gasto.administrativo'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Gastos administrativos" id="gastos_administrativos" ><i class="fa fa-file-text-o"></i><br><small>Gastos Administrativos</small></a> 
                                @endif
                                @if (Sentinel::hasAccess('atms.change.status.judicial'))
                                    <a class="btn btn-bitbucket btn-app-acciones" title="Cambiar de estado" id="cambio_estado_judicial" ><i class="fa fa-retweet"></i><br><small>Actualizar estado</small></a> 
                                @endif
                                <br>
                                <br>
                                <div class="modal-title" style="font-size: 15px; text-align: center">
                                    INTENTO DE COBRO | GESTIÓN ASEGURADORA
                                </div>
                                <br>
                                @if (Sentinel::hasAccess('atms.group.imputacion'))
                                <a class="btn btn-bitbucket btn-app-acciones" title="Imputacion de deudas" id="imputacion"  ><i class="fa fa-file-text-o"></i><br><small>Imputación de deudas</small></a> 
                                @endif
                                    @if (Sentinel::hasAccess('atms.change.status.aseguradora'))
                                    <a class="btn btn-bitbucket btn-app-acciones"  title="Cambiar de estado" id="cambio_estado_aseguradora"><i class="fa fa-retweet"></i><br><small>Actualizar estado </small></a> 
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





@endsection

@section('page_scripts')
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    @include('partials._delete_row_js')
@endsection

@section('js')
    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <script type="text/javascript">

        function view_options(group_id) {
            // gestion de cobranzas
            $('#pagare_cobranzas').attr('href', group_id + '/pagare')
            $('#rescision').attr('href', group_id + '/rescision');
            $('#retiro').attr('href', group_id + '/retiro');
            $('#cambio_estado').attr('href', group_id +'/change_status');
            $('#generar_factura').attr('href', group_id + '/penalizacion');
            //  $('#conbranzas').attr('href', group_id + '/cobranzas');

            // gestion de logisticas
            $('#generar_nota_retiro').attr('href', group_id + '/notaretiro');
            $('#retiro_dispositivo').attr('href', group_id + '/retiro_dispositivo');

            // gestion de compras
            $('#presupuesto').attr('href', group_id + '/presupuesto');           
            $('#cambio_estado_compras').attr('href', group_id +'/change_status');

            // gestion de legales -comercial
            $('#compromiso_pago').attr('href', group_id + '/compromiso');
            $('#intimacion').attr('href', group_id + '/intimacion');
            $('#cambio_estado_legales').attr('href', group_id +'/change_status');

            // gestion de legales -prejudicial
            $('#pagare_legales').attr('href', group_id + '/pagare')
            $('#cambio_estado_prejudicial').attr('href', group_id +'/change_status');

            // gestion de legales -judicial
             $('#remision').attr('href', group_id + '/remision');
             $('#perdida').attr('href', group_id + '/recibo_perdida');
             $('#ganancia').attr('href', group_id + '/recibo_ganancia');
             $('#gastos_administrativos').attr('href', group_id + '/gasto_administrativo');
             $('#cambio_estado_judicial').attr('href', group_id +'/change_status');

            // gestion de legales - aseguradora
            $('#imputacion').attr('href', group_id + '/imputacion');
            $('#cambio_estado_aseguradora').attr('href', group_id +'/change_status');
           

            $("#modal_comercial").modal();
        }


        $('.select2').select2();
        
        var data_table_config = {
            //custom
            //orderCellsTop: true,
            order: [[0, 'desc']], //ordenar por numero de columna
            fixedHeader: true,
            pageLength: 20,
            columnDefs: [{  // para definir el ancho de las columnas segun el target
                width: "10px",
                targets: 0 //columna de id
                },
                {
                    width: "40px",
                    //targets: 8 //columna de acciones
                }, 
            ],           
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            displayLength: 10,
            processing: true,
            sLoadingRecords: "Por favor espera - Cargando...",
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
               // $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        $('#datatable_1').DataTable(data_table_config);

    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        /*se agranda el modal para poder cargar el map*/
        @media screen and (min-width: 900px){
            .modal-large>.modal-dialog{
                width: 900px;
            }
        }
    </style>
    <style>
   
        /*boton de acciones*/
        .btn-app-conf{
            width: 50px; 
            height: 50px;
        }
        /*boton de acciones*/
        .btn-app-acciones{
            padding-top:10px;
            width: 120px; 
            height: 60px;
        }
       /* START - CONF SPINNER */
       table.dataTable thead {background-color:rgb(179, 179, 184)}
        .lds-roller {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }
        .lds-roller div {
            animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            transform-origin: 40px 40px;
        }
        .lds-roller div:after {
            content: " ";
            display: block;
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgb(64, 83, 255);
            margin: -4px 0 0 -4px;
        }
        .lds-roller div:nth-child(1) {
            animation-delay: -0.036s;
        }
        .lds-roller div:nth-child(1):after {
            top: 63px;
            left: 63px;
        }
        .lds-roller div:nth-child(2) {
            animation-delay: -0.072s;
        }
        .lds-roller div:nth-child(2):after {
            top: 68px;
            left: 56px;
        }
        .lds-roller div:nth-child(3) {
            animation-delay: -0.108s;
        }
        .lds-roller div:nth-child(3):after {
            top: 71px;
            left: 48px;
        }
        .lds-roller div:nth-child(4) {
            animation-delay: -0.144s;
        }
        .lds-roller div:nth-child(4):after {
            top: 72px;
            left: 40px;
        }
        .lds-roller div:nth-child(5) {
            animation-delay: -0.18s;
        }
        .lds-roller div:nth-child(5):after {
            top: 71px;
            left: 32px;
        }
        .lds-roller div:nth-child(6) {
            animation-delay: -0.216s;
        }
        .lds-roller div:nth-child(6):after {
            top: 68px;
            left: 24px;
        }
        .lds-roller div:nth-child(7) {
            animation-delay: -0.252s;
        }
        .lds-roller div:nth-child(7):after {
            top: 63px;
            left: 17px;
        }
        .lds-roller div:nth-child(8) {
            animation-delay: -0.288s;
        }
        .lds-roller div:nth-child(8):after {
            top: 56px;
            left: 12px;
        }
        @keyframes lds-roller {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        /* END - CONF SPINNER */

    </style>
@endsection
