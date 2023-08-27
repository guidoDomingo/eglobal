@extends('app')
@section('title')
    Descuento por Comision
@endsection
@section('content')
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles de la comision de : <label class="grupo"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Tipo de descuento</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Status Ondanet Afectado</th>
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
                    <h4 class="modal-title pull-left">Numero de recibo: <label class="nro_recibo"></label></h4>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
    <section class="content-header">
        <h1>
            Descuento por Comision
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Descuento por Comision</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('recibos_comisiones.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'recibos_comisiones.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Numero de recibo', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($comisiones)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Cliente</th>
                                        <th>Tipo de Recibo</th>
                                        <th>Monto</th>
                                        <th>Creado por</th>
                                        <th style="width:100px">Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($comisiones as $comision)
                                    <tr data-id="{{ $comision->id  }}">
                                        <td class="{{$comision->id}}">
                                            {{ $comision->id }}.
                                            <div class="btn-group">
                                                <buttom class="btn btn-default btn-xs" title="Mostrar atms">
                                                    <i class="pay-info fa fa-info-circle" style="cursor:pointer"></i>
                                                </buttom>
                                            </div>
                                        </td>
                                        <td>{{ $comision->cliente }}</td>
                                        <td>{{ $comision->description }}</td>
                                        <td>{{ number_format($comision->monto, 0)  }}</td>
                                        @if (isset($comision->createdBy->description))
                                            <td>{{ $comision->createdBy->description }}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        <td>{{ date('d/m/y H:i', strtotime($comision->created_at)) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $comisiones->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $comisiones->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['recibos_comisiones.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')
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

    $('.pay-info').on('click',function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var comision_id = row.data('id');
        console.log(comision_id);

        $("#modal-contenido").html('');
        $('#cargando').show();
        $(".nro_recibo").html('');
        $(".grupo").html('');

        $.get('/recibos_comisiones/info/' + comision_id, function(data) {
                //console.log(data);
                $(".grupo").html(data['grupo']);
                $(".nro_recibo").html(data['recibo_nro']);
                $("#modal-contenido").html(data['payment_info']);
                $("#detalles").show();
                $('#cargando').hide();
                $("#myModal").modal('show');
        });

        $("#myModal").modal('show');

    });
</script>
    {{-- @include('partials._delete_row_js') --}}
@endsection
