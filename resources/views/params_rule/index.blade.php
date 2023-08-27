@extends('layout')
@section('title')
    Reglas de parámetros
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Reglas de parámetros
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Reglas de parámetros</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('params_rules.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'params_rules.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Descripción', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($parametros)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="width:400px">Descripción</th>
                                    <th style="width:500px">Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($parametros as $parametro)
                                    <tr data-id="{{ $parametro->idparam_rules  }}">
                                        <td>{{ $parametro->idparam_rules }}.</td>
                                        <td>{{ $parametro->description }}</td>
                                        <td>{{ $parametro->type }}</td>
                                        
                                        <td>
                                            @if (Sentinel::hasAccess('params_rules.add|edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('params_rules.edit',['params_rule' => $parametro->idparam_rules])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('params_rules.delete'))
                                                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="{{ route('params_rules.destroy',['params_rule' => $parametro->idparam_rules])}}" ><i class="fa fa-remove"></i> </a>
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
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $parametros->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $parametros->appends(Request::only(['description']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['params_rules.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
