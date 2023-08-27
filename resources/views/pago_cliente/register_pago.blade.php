@extends('app')
@section('title')
    Confirmar Pago de Clientes
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Confirmar Pago de Clientes
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Confirmar Pago de Clientes</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        {!! Form::open(['route' => 'pago_clientes.migrate' , 'method' => 'POST', 'role' => 'form']) !!}
            <div id="myModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Detalles - Cliente : <label class="group_description"></label></h4>
                        </div>
                        <div class="modal-body">
                            <table id="detalles" class="table table-bordered table-hover dataTable" role="grid"
                                aria-describedby="Table1_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting_disabled" rowspan="1" colspan="1" width="150px">Atm a Afectar:</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-contenido">
                                    
                                </tbody>
                                <div style="display: none">
                                    {!! Form::text('id_atm', 0, ['id' => 'id_atm','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                    {!! Form::text('id_pago', 0, ['id' => 'id_pago','class' => 'form-control', 'readonly'=>'readonly']) !!}
                                </div>

                            </table>
                            <h4><label id="mensaje_deuda"></label></h4>
                        </div>
                        <div class="modal-footer">
                            <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                            <button type="button" style="display: none"
                                class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                            <button type="buttom" style="display: none"
                                class="devolucion btn btn-primary pull-left">Devolución</button>
        
                            <!--para ejecutar tareas de reproceso o devolucion -->
                            <button type="buttom" style="display: none" id="process_devolucion"
                                class="btn btn-primary pull-left">Enviar a devolución</button>
                            <button type="button" style="display: none" id="run_reprocesar"
                                class="btn btn-primary pull-left">Enviar a Reprocesar</button>
        
                            <!--para ejecutar inconsistencia -->
                            <button type="button" style="display: none" class="inconsistencia btn btn-primary pull-left">Generar
                                inconsistencia</button>
                            <button type="submit" style="display: none" id="process_comision"
                                class="btn btn-primary pull-left">Aceptar</button>
        
                            <!--para ejecutar reversiones ken -->
                            <button type="button" style="display: none" class="reversion_ken btn btn-primary pull-left">Generar
                                Reversion Ken</button>
                            <button type="buttom" style="display: none" id="process_reversion_ken"
                                class="btn btn-primary pull-left">Generar Reversion</button>
                            <!--para Cancelar sin hacer nada -->
                            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
        
                </div>
            </div>
        {!! Form::close() !!}
        @include('partials._flashes')
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'pago_clientes.register_pago', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Grupo', 'autocomplete' => 'off' ]) !!}
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
                                <th style="width:250px">Fecha de Generacion</th>
                                <th style="width:250px">Grupo</th>
                                <th style="width:250px">Monto</th>
                                <th style="width:200px">Generado por</th>
                                @if (\Sentinel::getUser()->hasAccess('superuser') || \Sentinel::getUser()->hasRole('mantenimiento.operativo') || \Sentinel::getUser()->hasRole('accounting.admin'))
                                <th style="width:250px">Modificado</th>
                                <th style="width:250px">Acciones</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($pagos as $pago)
                                    @if ( $pago->estado == null )
                                        <tr data-id="{{ $pago->id  }}">
                                            <td>{{ $pago->id }}</td>
                                            <td>{{ date('d/m/Y', strtotime($pago->created_at)) }}</td>
                                            <td>{{ $pago->group->description }}</td>
                                            <td>{{ number_format($pago->monto, 0) }}</td>
                                            @if (isset($pago->createdBy->description))
                                            <td>{{ $pago->createdBy->description }}</td>
                                            @else
                                            <td>-</td>
                                            @endif
                                            <td>{{ date('d/m/y H:i', strtotime($pago->updated_at)) }} @if($pago->updated_by != null) - 
                                                @if(!empty($pago->UpdatedBy->description)) 
                                                {{ $pago->UpdatedBy->description }}
                                                @endif 
                                                @endif
                                            </td>
                                            <td>
                                                <a class="btn btn-success btn-flat btn-row info" title="Migrar" href="#" ><i class="fa fa-check-circle-o fa-align-center"></i></a>
                                                <a class="btn btn-danger btn-flat btn-row btn-delete" title="No existe transferencia" href="#" ><i class="fa fa-remove"></i> </a></td>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach   
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $pagos->total() }}
                        registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $pagos->appends(Request::only(['id']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

@endsection
@section('js')

<!-- select2 -->
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    $('.select2').select2();

    $('.btn-migrate').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        swal({
            title: "Atención!",
            text: "Está a punto de ingresar el pago, está seguro?.",
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
                var url = '/reports/pago_clientes/migrate';
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
            text: "Está a punto de eliminar el registro, está seguro?.",
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
                var url = '/pago_clientes/delete';
                var type = "";
                var title = "";
                $.post(url,{_token: token,_id: id }, function(result){
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

    $('.info').on('click', function(e) {
        e.preventDefault();

        var row = $(this).parents('tr');
        var id = row.data('id');

        console.log('id: '+ id);
        $.get('/pago_clientes/get_atms/' + id, function(data) {
            console.log(data);
            $(".group_description").html(data['grupo']);
            
            $("#payment_details").hide();
            ///

            $("#detalles").show();
            $("#modal-contenido").html(data['payment_info']);
            $("#mensaje_deuda").hide();
            $('#process_comision').show();
            $('#id_pago').val(data['pago_id']);  
            $('#id_pago').trigger('change.select2');
            $('#atms').html(data['payment_info']);  
            $('#atms').trigger('change.select2');

            const select = document.getElementById('atm_select');

            select.addEventListener("change", e => {
                console.log(e.target.value);
                $('#id_atm').val(e.target.value);
                $('#id_atm').trigger('change.select2');
            });
               
            ///
            $("#myModal").modal();
            //botones
            $('.devolucion').hide();
            $('.reprocesar').hide();
            $('#process_devolucion').hide();
            $('.inconsistencia').hide();
            $('.reversion_ken').hide();
            $('#process_reversion_ken').hide();
            $('#run_reprocesar').hide();
        });
        
    });

</script>
    {{-- @include('partials._delete_row_js') --}}
@endsection
