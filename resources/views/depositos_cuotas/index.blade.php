@extends('layout')
@section('title')
    Deposito de Cuotas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Deposito de Cuotas
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Deposito de Cuotas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('depositos_cuotas.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'depositos_cuotas.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
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
                                @if (\Sentinel::getUser()->hasAccess('depositos_cuotas') && !\Sentinel::getUser()->hasAccess('superuser') && !\Sentinel::getUser()->hasRole('mantenimiento.operativo') && !\Sentinel::getUser()->hasRole('accounting.admin'))    
                                    @foreach($depositocuotas as $depositoboleta)
                                        @if ($depositoboleta->estado == null && $depositoboleta->tipo_recibo_id == 1)    
                                            @if (date('d/m/y', strtotime($depositoboleta->created_at)) == date('d/m/y', strtotime(Carbon\Carbon::today() )) )
                                                @if ( \Sentinel::getUser()->id == $depositoboleta->user_id)
                                                    <tr data-id="{{ $depositoboleta->id  }}">
                                                        <td>{{ $depositoboleta->id }}</td>
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
                                    @foreach($depositocuotas as $depositoboleta)
                                        @if ( $depositoboleta->estado == null && $depositoboleta->tipo_recibo_id == 1)
                                            <tr data-id="{{ $depositoboleta->id  }}">
                                                <td>{{ $depositoboleta->id }}</td>
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
                                                <td>{{ date('d/m/y H:i', strtotime($depositoboleta->updated_at)) }} 
                                                    @if($depositoboleta->updated_by != null) 
                                                    - @if (!empty($depositoboleta->UpdatedBy->description))
                                                    <td>{{ $depositoboleta->createdBy->description }}</td>
                                                    @else
                                                    <td>-</td>
                                                    @endif @endif</td>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $depositocuotas->total() }}
                        registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $depositocuotas->appends(Request::only(['id']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
    {!! Form::open(['route' => ['depositos_cuotas.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
                var url = '/reports/depositos_cuotas/migrate';
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
                var url = '/reports/depositos_cuotas/delete';
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
</script>
    {{-- @include('partials._delete_row_js') --}}
@endsection
