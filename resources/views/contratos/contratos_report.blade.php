
@extends('layout')
@section('title')
    Contratos | Reporte
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Reporte de contratos
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Legales</a></li>
            <li class="active">Reporte</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')

        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filtros de búsqueda</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <form action="{{ route('reports.contratos.search') }}" method="GET">
                        <div class="box-body" style="display: block;">
    
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('groups', 'Grupos') !!}
                                        {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id', 'class' => 'form-control select2']) !!}
                                    </div>
                                </div>

                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('atm', 'ATMs') !!}
                                        {!! Form::select('atm_id', $atms, $atm_id , ['id' => 'atm_id','class' => 'form-control select2']) !!}
                                    </div>
                                </div> --}}

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rango de vigencia:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime" class="form-control" value="{{$reservationtime_contract}}"  placeholder="__/__/____" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('status', 'Estado del contrato') !!}
                                         
                                        {!! Form::select('status',[ '0'=> 'Todos', '1' =>'Recepcionado', '2' => 'Activo', '3' =>'Inactivo', '4' =>'Vencido'],$status, ['id' => 'status','class' => 'form-control select2']) !!}
                                  
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-block btn-primary" name="search"
                                            value="search" id="buscar">BUSCAR</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-block btn-success" name="download"
                                            value="download">EXPORTAR</button>
                                    </div>
                                </div>
                                       
                            </div>
    
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if (isset($contratos))
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resultado de la búsqueda</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                        <div>
                            <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                            Cargando...

                            <p id="rows_loaded" title="Filas cargadas"></p>
                        </div>
                    </div>
                    <div id="content" style="display: none" class="col-xs-12">
                        <table id="detalles" class="table table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nro de Contrato</th>
                                    <th>Tipo</th>
                                    <th>Grupo</th>
                                    <th>Linea de crédito</th>
                                    <th>Estado</th>
                                    <th>Vigencia</th>
                                    <th>Días Restantes</th>
                                    <th>Recepción</th>
                                    <th>Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contratos as $contrato)
                                    <tr data-id="{{ $contrato->id_contract  }}">
                                        <td>{{ $contrato->id_contract }}</td>
                                        <td>{{ $contrato->number_contract }}</td>
                                        <td>{{ $contrato->description_contract_type }}</td>
                                        <td>{{ $contrato->group_ruc }} - {{ $contrato->group_description }}</td>
                                        <td>{{ number_format($contrato->credit_limit) }}</td>
                                        @if ( $contrato->status == 1)
                                            <td>RECEPCIONADO</td>
                                        @elseif ($contrato->status == 2)                                     
                                            <td>ACTIVO</td>
                                        @elseif ($contrato->status == 3)
                                            <td>INACTIVO</td>
                                        @elseif ($contrato->status == 4)
                                            <td>VENCIDO</td>
                                        @endif 
                                        <td>{{ date('d/m/Y', strtotime($contrato->date_init)).' - '. date('d/m/Y', strtotime($contrato->date_end)) }}</td>
                                        <td>{{ $contrato->restantes }}</td>
                                        
                                        @if ($contrato->reception_date == null)
                                            <td> - </td>
                                        @else
                                            <td>{{ date('d/m/Y', strtotime($contrato->reception_date))}}</td>
                                        @endif

                                        @if ($contrato->fecha_aprobacion == null)
                                            <td> - </td>
                                        @else
                                            <td>{{ date('d/m/Y', strtotime($contrato->fecha_aprobacion))}}</td>
                                        @endif

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </section>
   

@endsection
@section('js')
<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<script type="text/javascript">
//Datatable config
var data_table_config = {
        //custom
        bAutoWidth: false,
        aoColumns : [
            { sWidth: "3%", "targets": 0, className: "text-center"},
            { sWidth: "8%", "targets": 1, className: "text-center"},
            { sWidth: "10%", "targets": 2},
            { sWidth: "40%", "targets": 3},
            { sWidth: "10%", "targets": 4, className: "text-center"},
            { sWidth: "10%", "targets": 5, className: "text-center"},
            { sWidth: "10%", "targets": 6, className: "text-center"},
            { sWidth: "10%", "targets": 7, className: "text-center"},
            { sWidth: "10%", "targets": 8, className: "text-center"},
            { sWidth: "10%", "targets": 9, className: "text-center"},

            ],
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 10,
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
            // $('body > div.wrapper > header > nav > a').trigger('click');
        }
        
    }

    var table = $('#detalles').DataTable(data_table_config); 
</script>
<script type="text/javascript">
    $('.select2').select2();

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
        startDate: moment(),
        endDate: moment().add(12,'months'),
    });

    
</script>

@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection
