@extends('layout')
@section('title')
    Caracteristicas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Cliente: {{ $grupo->ruc .' | '. $grupo->description}}
            <small>Caracteristicas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Caracteristicas</a></li>
            <li class="active">Ver</li>

        </ol>
    </section>
    <section class="content">
        {{-- @include('partials._flashes') --}}
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                @if (Sentinel::hasAccess('caracteristicas_clientes.add'))
                    <a href="{{ route('caracteristicas.clientes.create',['caracteristica' => $group_id]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                @endif
                <a class="btn-default btn-sm" href="{{ route('caracteristicas.clientes.index',['caracteristica' => $group_id]) }}" role="button">Cancelar</a>
               
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($caracteristicas)
                            <table id="detalles" class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th style="text-align:center;">Banco</th>
                                        <th style="text-align:center;">Cuenta Bancaria</th>
                                        <th style="text-align:center;">Referencia</th>
                                        <th style="text-align:center;">Canal</th>
                                        <th style="text-align:center;">Categoria</th>
                                        <th style="text-align:center;">Permite POP</th>
                                        <th style="text-align:center;">Tiene POP</th>
                                        <th style="text-align:center;">Tiene Netel</th>

                                        <th style="text-align:center;">Tiene Bancard</th>
                                        <th style="text-align:center;">Tiene Pronet</th>
                                        <th style="text-align:center;">POS Dinelco</th>
                                        <th style="text-align:center;">POS Bancard</th>
                                        <th style="text-align:center;">Tiene Billetaje</th>

                                        <th style="text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($caracteristicas as $item)
                                    <tr data-id="{{ $item->id  }}">
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->id }}.</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->banco }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->numero_cuenta }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->referencia }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->canal }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ $item->categoria }}</td>
                                        @if (  $item->permite_pop == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_pop == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_netel == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_bancard == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_pronet == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_pos_dinelco == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        @if (  $item->tiene_pos_bancard == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        
                                        @if (  $item->tiene_billetaje == TRUE)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif

                                        <td style="text-align:center; width: 170px; vertical-align: middle;">
                                            @if (Sentinel::hasAccess('caracteristicas_clientes.edit'))
                                                <a class="btn-sm btn-success btn-flat btn-row" title="Editar Caracteristica" href="{{ route('caracteristicas.edit',['id' => $item->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('caracteristicas_clientes.delete'))
                                                <a class="btn-delete btn-sm btn-danger btn-flat btn-row" title="Eliminar Caracteristica" href="#" ><i class="fa fa-remove"></i> </a>  
                                            @endif

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
    {!! Form::open(['route' => ['caracteristicas.delete',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
                //var form = $('#form-delete');
                var url = '/caracteristicas/clientes/delete';
                var type = "";
                var title = "";

                // var url = form.attr('action').replace(':ROW_ID',id);
                // var data = form.serialize();
                // var type = "";
                // var title = "";
                $.post(url,{_token: token,_id: id}, function(result){
                    if(result.error == false){
                        // row.fadeOut();
                        type = "success";
                        title = "Operación realizada!";
                        swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });

                    }else{
                        type = "error";
                        title =  "No se pudo realizar la operación"
                        swal(result.message);

                    }
                    location.reload();

                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
            }
        });
    });
</script>


@if (session('guardar') == 'ok')
<script>
    swal({
            position: 'top-end',
            type: 'success',
            title: 'El registro ha sido guardado existosamente.',
            showConfirmButton: false,
            timer: 1500
            })
</script>
@endif
@if (session('actualizar') == 'ok')
<script>
    swal({
            position: 'top-end',
            type: 'success',
            title: 'El registro ha sido actualizado existosamente.',
            showConfirmButton: false,
            timer: 1500
            })
</script>
@endif
@endsection



@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <style>
        /* START - CONF SPINNER */
        table.dataTable thead {background-color:rgb(179, 179, 184)}
        
     </style>
@endsection
