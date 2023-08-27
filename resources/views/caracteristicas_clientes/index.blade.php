@extends('layout')
@section('title')
    Clientes
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Clientes
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Clientes</a></li>
            <li class="active">Caracteristicas de clientes</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
               
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($clientes)
                            <table id="detalles" class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th style="text-align:center;">Ruc</th>
                                        <th style="text-align:center;">Cliente</th>
                                        <th style="text-align:center;">Dirección</th>
                                        <th style="text-align:center;">Teléfono</th>
                                        <th style="text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientes as $item)
                                        <tr data-id="{{ $item->id  }}">
                                            <td style="text-align:center; vertical-align: middle;">{{ $item->id }}.</td>
                                            <td style="text-align:center; vertical-align: middle;">{{ $item->ruc }}</td>
                                            <td style="text-align:center; vertical-align: middle;">{{ $item->description }}</td>
                                            <td style="text-align:center; vertical-align: middle;">{{ $item->telefono }}</td>
                                            <td style="text-align:center; vertical-align: middle;">{{ $item->direccion }}</td>
                                            <td style="text-align:center; width: 170px; vertical-align: middle;">
                                                <a class="btn-sm btn-primary btn-flat btn-row" title="Ver Caracteristicas" href="{{ route('caracteristicas.show',['id' => $item->id])}}"><i class="fa fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
         
        </div>
    </section>
    {!! Form::open(['route' => ['campaigns.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}



@endsection
@section('js')
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script>
   $(document).ready(function () {
        $('#detalles').DataTable({
            "columnDefs": [{
            "targets": 0
            }],
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ resultados",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningun dato disponible en esta tabla",
                "sInfo": "Mostrando resultados _START_-_END_ de  _TOTAL_",
                "sInfoEmpty": "Mostrando resultados del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar ",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Ultimo",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
            "displayLength": 15,
            "processing": true,
            "serverSide": true,
            }
        });
    });
</script>

@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <style>
        /* START - CONF SPINNER */
        table.dataTable thead {background-color:rgb(179, 179, 184)}
        
     </style>
@endsection
