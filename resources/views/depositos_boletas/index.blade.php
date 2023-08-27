@extends('layout')
@section('title')
    Deposito de Boletas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Deposito de Boletas
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Deposito de Boletas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        <!-- Modal -->
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Detalles - Boleta ID : <label class="idTransaccion"></label></h4>
                    </div>
                    <div class="modal-body">
                        <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                            <thead>
                            <tr role="row">
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th id="rechazo_detail" rowspan="1" colspan="1">Detalles del Rechazo</th>
                                <th id="recibo_detail" rowspan="1" colspan="1">Detalles del Recibo Generado</th>
                            </tr>
                            </thead>
                            <tbody id="modal-contenido">
                            </tbody>
                        </table>
                        <div id="status_description"></div>
                        <table id="payment_details" style="display: none;" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table2_info">                                             <thead>
                            <tr role="row">
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Valor a pagar</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Valor recibido</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Valor devuelto</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Fecha</th>
                            </tr>
                            </thead>
                            <tbody id="modal-contenido-payments">

                            </tbody>
                            <tfoot>
                            <tr>
                                <th style="display:none;" rowspan="1" colspan="1"></th>
                                <th style="display:none;" rowspan="1" colspan="1"></th>
                                <th rowspan="1" colspan="1">Valor a pagar</th>
                                <th rowspan="1" colspan="1">Valor recibido</th>
                                <th rowspan="1" colspan="1">Valor devuelto</th>
                                <th rowspan="1" colspan="1">Fecha</th>
                            </tr>
                            </tfoot>
                        </table>
                        <div id="devoluciones" style="display: none">
                            <div id="keys_spinn" class="text-center" style="margin: 50px 10px; display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>
                            <div id="message_box" class="display: none;"></div>
                            <form role="form" id="devolucion-form" enctype="multipart/form-data">
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="txtDescription">Descripción</label>
                                    <textarea id="txtDescription" name="txtDescription" class="form-control" rows="3" placeholder="Describa brevemente el caso ..."></textarea>
                                    <input type="hidden" id="txttransaction_id">
                                </div>
                                <div class="form-group">
                                    <label for="fuComprobante">Adjunte un comprobante</label>
                                    <input type="file" id="fuComprobante" name="fuComprobante">

                                    <p class="help-block">El archivo debe ser una imagen.</p>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </form>
                        </div>
                        <div id="reprocesos" style="display: none">
                            <div id="reprocesar-info">
                                <p><b>Servicio  :</b> <span id="service_description"></span></p>
                                <p><b>Monto:</b>      <span id="transaction_amount"></span></p>
                                <p><b>Referencia:</b> <span id="transaction_referece"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                        <button type="button" style="display: none" class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                        <button type="buttom" style="display: none" class="devolucion btn btn-primary pull-left">Devolución</button>

                        <!--para ejecutar tareas de reproceso o devolucion -->
                        <button type="buttom" style="display: none" id="process_devolucion" class="btn btn-primary pull-left">Enviar a devolución</button>
                        <button type="button" style="display: none" id="run_reprocesar"class="btn btn-primary pull-left">Enviar a Reprocesar</button>
                        <!--para Cancelar sin hacer nada -->
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>
        @include('partials._flashes')
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('depositos_boletas.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'depositos_boletas.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Numero de Boleta', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-striped">
                            <tbody>
                            <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>ATM</th>
                                <th>Fecha de la boleta</th>
                                <th>Tipo de Pago</th>
                                <th style="width:75px">Banco</th>
                                <th>Cuenta Bancaria</th>
                                <th>Numero de Boleta</th>
                                <th>Monto</th>
                                <th style="width:150px">Depositado por</th>
                                @if (\Sentinel::getUser()->hasAccess('superuser') || \Sentinel::getUser()->hasRole('mantenimiento.operativo') || \Sentinel::getUser()->hasRole('accounting.admin'))
                                <th style="width:125px">Modificado</th>
                                <th style="width:175px">Acciones</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                                @if (\Sentinel::getUser()->hasAccess('depositos_boletas') && !\Sentinel::getUser()->hasAccess('superuser') && !\Sentinel::getUser()->hasRole('mantenimiento.operativo') && !\Sentinel::getUser()->hasRole('accounting.admin'))    
                                    @foreach($depositoboletas as $depositoboleta)
                                        @if ($depositoboleta->estado == null)
                                        @if (date('d/m/y', strtotime($depositoboleta->created_at)) == date('d/m/y', strtotime(Carbon\Carbon::today() )) )
                                            @if ( \Sentinel::getUser()->id == $depositoboleta->user_id)
                                            <tr data-id="{{ $depositoboleta->id  }}">
                                                <td>{{ $depositoboleta->id }}<br>
                                                    @if(!is_null($depositoboleta->imagen_asociada))
                                                        <buttom class="btn btn-default btn-xs" title="Ver Imagen">
                                                            <i class="info_imagen fa fa-camera" style="cursor:pointer"></i>
                                                        </buttom>
                                                    @endif
                                                </td>
                                                <td>{{ $depositoboleta->name }}</td>
                                                <td>{{ date('d/m/y', strtotime($depositoboleta->fecha)) }}</td>
                                                <td>{{ $depositoboleta->tipoPago->descripcion }}</td>
                                                <td>{{ $depositoboleta->cuentaBancaria->banco->descripcion }}</td>
                                                <td>{{ $depositoboleta->cuentaBancaria->numero_banco }}</td>
                                                <td>{{ $depositoboleta->boleta_numero }}</td>
                                                <td>{{ number_format($depositoboleta->monto, 0)  }}</td>
                                                @if (isset($depositoboleta->createdBy->description))
                                                <td>{{ $depositoboleta->createdBy->description }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                            </tr>
                                            @endif
                                        @endif    
                                        @endif
                                    @endforeach
                                @elseif (\Sentinel::getUser()->hasAccess('superuser') || \Sentinel::getUser()->hasRole('mantenimiento.operativo') || \Sentinel::getUser()->hasRole('accounting.admin'))    
                                    @foreach($depositoboletas as $depositoboleta)
                                        @if ( $depositoboleta->estado == null )
                                            <tr data-id="{{ $depositoboleta->id  }}">
                                                <td>{{ $depositoboleta->id }}<br>
                                                    @if(!is_null($depositoboleta->imagen_asociada))
                                                        <buttom class="btn btn-default btn-xs" title="Ver Imagen">
                                                            <i class="info_imagen fa fa-camera" style="cursor:pointer"></i>
                                                        </buttom>
                                                    @endif
                                                </td>
                                                <td>{{ $depositoboleta->name }}</td>
                                                <td>{{ date('d/m/y', strtotime($depositoboleta->fecha)) }}</td>
                                                <td>{{ $depositoboleta->tipoPago->descripcion }}</td>
                                                <td>{{ $depositoboleta->cuentaBancaria->banco->descripcion }}</td>
                                                <td>{{ $depositoboleta->cuentaBancaria->numero_banco }}</td>
                                                <td>{{ $depositoboleta->boleta_numero }}</td>
                                                <td>{{ number_format($depositoboleta->monto, 0) }}</td>
                                                @if (isset($depositoboleta->createdBy->description))
                                                <td>{{ $depositoboleta->createdBy->description }}</td>
                                                @else
                                                <td>-</td>
                                                @endif
                                                <td>{{ date('d/m/y H:i', strtotime($depositoboleta->updated_at)) }} @if($depositoboleta->updated_by != null) - 
                                                    @if(!empty($depositoboleta->UpdatedBy->description)) 
                                                    {{ $depositoboleta->UpdatedBy->description }}
                                                    @endif 
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-success btn-flat btn-row btn-migrate" title="Migrar" href="#" ><i class="fa fa-check-circle-o fa-align-center"></i></a>
                                                    <a class="btn btn-danger btn-flat btn-row btn-delete" title="No existe transferencia" href="#" ><i class="fa fa-remove"></i> </a></td>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach   
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $depositoboletas->total() }}
                        registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $depositoboletas->appends(Request::only(['id']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
    {!! Form::open(['route' => ['depositos_boletas.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('js')
<script type="text/javascript">
    $('.btn-migrate').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        swal({
            title: "Atención!",
            text: "Está a punto de migrar el registro, está seguro?.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#449d44",
            confirmButtonText: "Si, migrar!",
            cancelButtonText: "No, cancelar!",
            closeOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm){
            if (isConfirm) {
                var url = '/reports/deposito_boletas/migrate';
                var type = "";
                var title = "";
                $.post(url,{_token: token,_id: id}, function(result){
                    if(result.error == false){
                        type = "success";
                        title = "Operación realizada!";
                        
                    }else{
                        type = "error";
                        title =  "No se pudo realizar la operación"
                    }
                    //swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                    location.reload();
                    
                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
            }
        });
    });

    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        swal({
            title: "Atención!",
            //text: "Está a punto de eliminar el registro, está seguro?.",
            text: "<p style='text-orientation: center; color: black'>Motivo del Rechazo</p><br><textarea id='text' style='color: black'></textarea>",
            html: true,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#449d44",
            confirmButtonText: "Si, eliminar!",
            cancelButtonText: "No, cancelar!",
            closeOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm){
            if (isConfirm) {
                /*var form = $('#form-delete');
                var url = form.attr('action').replace(':ROW_ID',id);
                var data = form.serialize();
                console.log(data);
                console.log(url);
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
                });*/
                var description=document.getElementById('text').value;
                var url = '/reports/deposito_boletas/delete';
                var type = "";
                var title = "";
                $.post(url,{_token: token,_id: id, _description: description }, function(result){
                    if(result.error == false){
                        type = "success";
                        title = "Operación realizada!";
                        
                    }else{
                        type = "error";
                        title =  "No se pudo realizar la operación"
                        swal(result.message);
                    }
                    //swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                    location.reload();
                    
                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
            }
        });
    });

    $('.info_imagen').on('click',function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');

        $.get('{{ url('reports') }}/info/details_imagen/' + id, function(data) {
            $(".idTransaccion").html(id);
            $("#modal-contenido").html(data['imagen']);
            $("#status_description").hide();
            $("#payment_details").hide();
            $("#detalles").show();
            //$("#detalles_recibo").hide();
            $('#rechazo_detail').hide();
            $('#recibo_detail').hide();
            $('#devoluciones').hide();
            $('#reprocesos').hide();
            $("#myModal").modal();
            //botones
            $('.devolucion').hide();
            $('.reprocesar').hide();
            $('#process_devolucion').hide();
            $('#run_reprocesar').hide();
        });
    });
</script>
    {{-- @include('partials._delete_row_js') --}}
@endsection
