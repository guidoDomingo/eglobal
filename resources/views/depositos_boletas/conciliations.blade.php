@extends('layout')
@section('title')
    Conciliaciones de Boletas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Conciliaciones
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Deposito de Boletas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>

    <section class="content">
        @include('partials._flashes')
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Conciliaciones de Boletas de Deposito</h3>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'boletas.conciliations', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Numero de Boleta', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody><thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Sucursal</th>
                                <th>Numero de Boleta</th>
                                <th>Monto</th>
                                <th>Tipo de Pago</th>
                                <th>Depositado por</th>
                                <th>Mensaje</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <!--<th style="width:100px">Acciones</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($deposits as $deposit)
                            <tr data-id="1">
                                <td>{{$deposit->id}}</td>
                                <td>{{$deposit->sucursal}}</td>
                                <td>{{$deposit->nroboleta}}</td>
                                <td>{{$deposit->monto}}</td>
                                <td>{{$deposit->description}}</td>
                                <td>{{$deposit->username}}</td>
                                <td>{{$deposit->response}}</td>
                                <td>{{$deposit->created_at}}</td>
                                <td>{{$deposit->updated_at}}</td>
                                <!--<td></td>-->
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite"> {{count($deposits)}} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['depositos_boletas.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
