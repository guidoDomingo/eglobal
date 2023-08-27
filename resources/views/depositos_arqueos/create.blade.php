@extends('layout')

@section('title')
    Nuevo depósito de Arqueo
@endsection
@section('content')
<section class="content-header">
    <h1>
        Depósito de Arqueo
        <small>Registro de deposito de Arqueos</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li><a href="#">Depósito de Arqueos</a></li>
        <li class="active">Agregar</li>
    </ol>
</section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Depósito de Arqueos</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'depositos_arqueos.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('depositos_arqueos.partials.fields')
                    </div>
                    

                    <div id='messages' style="display: none"></div>   
            <div class="box" id="transactions" style='display:none'>
                
                <div class="box-header">
                    <h3 class="box-title">Arqueos</h3>                        
                </div>
                <div class="box-body  no-padding">
                    <div class="row">
                        <div class="col-xs-12" style="overflow-x: auto">
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID Transacción</th>
                                    <th>Sede</th>
                                    <th>Monto</th>
                                    <th><input type="checkbox" id="all" checked> Todos</th>
                                    <th>Estado</th>
                                    <th>Fecha de arqueo</th>
                                    <th>Usuario</th>                            
                                    <th>Nro Documento</th>
                                    <th>Acciones</th>                            
                                </tr>
                                </thead>
                                <tbody id='transaction_body'>
                                                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="box-footer clearfix">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="dataTables_info" role="status" aria-live="polite">Total a depositar: <span id="total" style="font-weight: bold"></span></div>
                        </div>
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('depositos_arqueos.index') }}" role="button">Cancelar</a>
                        <button type="submit" value="submit" class="btn btn-primary submit">Guardar</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <div id="EditMontoModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Transaccion Nro : <label class="idTransaccion"></label></h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('amount', 'Monto', ['id' => 'label1', 'name' => 'label1']) !!}            
                                        {!! Form::text('amount', null , ['onkeyup' => 'format(this)', 'onchange' => 'format(this)' , 'id' => 'amount', 'class' => 'form-control', 'placeholder' => 'Ingresá el monto', 'required' => 'true' ]) !!}
                                    </div>
                                </div>
                            </div>  
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('motivo', 'Motivo', ['id' => 'label1', 'name' => 'label1']) !!}            
                                        {!! Form::textarea('motivo', null , ['id' => 'motivo', 'rows' => 4, 'class' => 'form-control', 'placeholder' => 'Ingresá un breve motivo de la edición del monto', 'required' => 'true' ]) !!}
                                    </div>
                                </div>
                            </div>  
                        </div>
                        <div class="modal-footer">
                            <button id='edit_amount' type="button" class="btn btn-success" data-dismiss="modal">Guardar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
        
                </div>
        </div>                                

    </section>    
@endsection
@include('depositos_arqueos.partials.form_js')