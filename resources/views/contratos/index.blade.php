
@extends('layout')
@section('title')
    Contratos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Contratos
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li class="active">Contratos</li>
        </ol>
    </section>
    <section class="content">
        {{-- @include('partials._flashes') --}}
        <div class="box box-primary">
            <div class="box-header">
                @if (Sentinel::hasAccess('gestor_contratos.add'))
                    <a href="{{ route('contracts.create') }}" class="btn-sm btn-primary active" role="button"  style="float: left;">Agregar</a>
                @endif
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

                    @if ($contratos)
                        <table id="detalles" class="table table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th style="text-align:center; vertical-align:middle;width:10px">#</th>
                                <th style="text-align:center; vertical-align:middle;">Grupo</th>
                                <th style="text-align:center; vertical-align:middle;">Nro de Contrato</th>
                                <th style="text-align:center; vertical-align:middle;">Linea de crédito</th>
                                <th style="text-align:center; vertical-align:middle;">Estado</th>
                                <th style="text-align:center; vertical-align:middle;">Vigencia</th>
                                <th style="text-align:center; vertical-align:middle; width:100px">Modificar</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($contratos as $contrato)
                                <tr data-id="{{ $contrato->contract_id  }}">
                                    <td style="text-align:center; vertical-align:middle;">{{ $contrato->grupo_id }}</td>
                                    <td style="text-align:center; vertical-align:middle; width:50%">{{ $contrato->grupo_ruc }} - {{ $contrato->grupo_name }}</td>
                                    <td style="text-align:center; vertical-align:middle;">{{ $contrato->contract_number }}</td>
                                    <td style="text-align:center; vertical-align:middle;">{{ number_format($contrato->contract_credit) }}</td>
                                    @if ( $contrato->contract_stats == 1)
                                        <td style="text-align:center; vertical-align: middle;">RECEPCIONADO</td>
                                    @elseif ($contrato->contract_stats == 2)                                     
                                        <td style="text-align:center; vertical-align: middle;">ACTIVO</td>
                                    @elseif ($contrato->contract_stats == 3)
                                        <td style="text-align:center; vertical-align: middle;">INACTIVO</td>
                                    @elseif ($contrato->contract_stats == 4)
                                        <td style="text-align:center; vertical-align: middle;">VENCIDO</td>
                                    @endif 
                                    <td style="text-align:center; vertical-align: middle;">{{ date('d/m/Y', strtotime($contrato->fecha_init)).' - '. date('d/m/Y', strtotime($contrato->fecha_end)) }}</td>

                                    <td style="text-align:center; vertical-align:middle; width:1%">
                                        @if (Sentinel::hasAccess('gestor_contratos.edit'))
                                            <a class="btn btn-warning btn-flat btn-row btn-sm" title="Editar" href="{{ route('contracts.edit',['contract' => $contrato->contract_id])}}"><i class="fa fa-pencil"></i></a>
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
    </section>
    {!! Form::open(['route' => ['contracts.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
   $(document).ready(function () {

        var data_table_config = {
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
        var table = $('#detalles').DataTable(data_table_config);
    });
</script>
@if (session('error') == 'ok')
    <script>
        Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Hubo un error al generar la compra.',
                showConfirmButton: false,
                timer: 1500
                })
    </script>
@endif
@if (session('guardar') == 'ok')
    <script>
        Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'El registro ha sido guardado existosamente.',
                showConfirmButton: false,
                timer: 1500
                })
    </script>
@endif
@if (session('actualizar') == 'ok')
    <script>
        Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'El registro ha sido actualizado existosamente.',
                showConfirmButton: false,
                timer: 1500
                })
    </script>
@endif
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

