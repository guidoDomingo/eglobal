
@extends('layout')
@section('title')
    Gestor de Pólizas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Gestor de Pólizas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li class="active">Pólizas</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box box-primary">
   
            <div class="box-body">
                {{-- <div class="row"> --}}
                    <div class="col-xs-12">
                        @if ($polizas)
                            <table id="detalles" class="table table-bordered table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th style="text-align:center; vertical-align:middle;width:10px">ID</th>
                                    <th style="text-align:center; vertical-align:middle;">Ruc</th>
                                    <th style="text-align:center; vertical-align:middle;">Grupo</th>
                                    {{-- <th style="text-align:center; vertical-align:middle;">Endoso</th> --}}
                                    <th style="text-align:center; vertical-align:middle;">Número de Póliza</th>
                                    <th style="text-align:center; vertical-align:middle;">Tipo de Póliza</th>
                                    <th style="text-align:center; vertical-align:middle;">Capital</th>
                                    <th style="text-align:center; vertical-align:middle;">Linea operativa</th>
                                    <th style="text-align:center; vertical-align:middle;">Estado</th>
                                    <th style="text-align:center; vertical-align:middle;">Fecha de creación</th>
                                    <th style="text-align:center; vertical-align:middle; width:100px">Modificar</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($polizas as $poliza)
                                    <tr data-id="{{ $poliza->id  }}">
                                        <td style="text-align:center; vertical-align:middle;">{{ $poliza->id }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $poliza->grupo_ruc }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $poliza->grupo }}</td>
                                        {{-- <td style="text-align:center; vertical-align:middle;">{{ $poliza->insurance_code }}.</td> --}}
                                        <td style="text-align:center; vertical-align:middle;">{{ $poliza->number }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $poliza->tipo}}</td>
                                        {{-- <td style="text-align:center; vertical-align:middle;">{{ $poliza->tipo->description}}</td> --}}
                                        <td style="text-align:center; vertical-align:middle;">{{ number_format($poliza->capital) }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ number_format($poliza->capital_operativo) }}</td>

                                        @if ( $poliza->status == 1)
                                            <td style="text-align:center; vertical-align: middle;">RECEPCIONADO</td>
                                        @elseif ($poliza->status == 2)                                     
                                            <td style="text-align:center; vertical-align: middle;">ACTIVO</td>
                                        @elseif ($poliza->status == 3)
                                            <td style="text-align:center; vertical-align: middle;">INACTIVO</td>
                                        @elseif ($poliza->status == 4)
                                            <td style="text-align:center; vertical-align: middle;">VENCIDO</td>
                                        @endif
                                        <td style="text-align:center; vertical-align: middle;">{{ date('d/m/Y H:i:s', strtotime($poliza->created_at)) }}</td>

                                        <td style="text-align:center; vertical-align:middle;">
                                            @if (Sentinel::hasAccess('insurances_form.edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('insurances.edit',['insurance' => $poliza->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['insurances.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
            "iDisplayLength": 50, 
            "processing": true,
            "serverSide": true,
            }
        });
    });
</script>
<script type="text/javascript">
    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        swal({
            title: "Atención!",
            text: "Está a punto de borrar el registro, está seguro?.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Si, eliminar!",
            cancelButtonText: "No, cancelar!",
            closeOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm){
            if (isConfirm) {
                var form = $('#form-delete');
                var url = form.attr('action').replace(':ROW_ID',id);
                var data = form.serialize();
                var type = "";
                var title = "";
                $.post(url,data, function(result){
                    if(result.error == false){
                        row.fadeOut();
                        type = "success";
                        title = "Operación realizada!";
                    }else{
                        type = "error";
                        title =  "No se pudo realizar la operación"
                    }
                    swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
            }
        });
    });
</script>
@endsection

