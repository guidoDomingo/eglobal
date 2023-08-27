@extends('layout')
@section('title')
Gestor de dropbox
@endsection
@section('content')
<section class="content-header">
  <h1>
    Generar token para dropbox
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Aplicaciones</a></li>
    <li class="active">Generar token</li>
  </ol>
</section>
<section class="content">
    <div class="box box-primary">
        @include('partials._flashes')
        <div class="box-header">            
            {{-- <a href="{{ route('token_dropbox.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a> --}}
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <table id="detalles" class="table table-bordered table-condensed table-hover">
                        <thead>
                            <tr>    
                                <th>ID</th>
                                <th style="text-align:center; vertical-align:middle;">DESCRIPCIÓN</th>
                                <th style="text-align:center; vertical-align:middle;">TOKEN</th>
                                <th style="text-align:center; vertical-align:middle;">PUBLICADO</th>                                
                                <th style="text-align:center; vertical-align:middle;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($token as $item)
                            <tr data-id="{{ $item->id  }}">                                
                                <td width="3%">{{ $item->id }}</td>
                                <td width="10%">{{ $item->name }}</td>
                                <td width="60%">{{ $item->hash }}</td>
                                <td style="text-align:center; vertical-align:middle;">{{ date('d/m/y H:i', strtotime($item->created_at)) }}</td>
                                <td style="text-align:center; vertical-align:middle;">
                                    @if (Sentinel::hasAccess('token_dropbox.add|edit'))
                                        <a class="btn btn-success btn-flat btn-row btn-sm" title="Editar" href="{{ route('token_dropbox.edit',['id' => $item->id])}}"><i class="fa fa-pencil"></i></a>
                                    @endif
                                    @if (Sentinel::hasAccess('token_dropbox.delete'))
                                        <a class="btn-delete btn btn-danger btn-flat btn-row btn-sm" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                    @endif
                                </td>
                            </tr>                            
                            @endforeach
                        </tbody>    
                    </table>
                </div>    
            </div>            
        </div> 
         
    </div>
</section>
{!! Form::open(['route' => ['token_dropbox.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}
@endsection

@section('page_scripts')
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script>
   $(document).ready(function () {

        $('#detalles').DataTable({
            "columnDefs": [{ width: '5%', targets: 0 }],
            //"fixedColumns": true,
            //scrollY:        "300px",
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
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
@endsection