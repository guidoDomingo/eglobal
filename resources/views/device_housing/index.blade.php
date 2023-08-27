@extends('app')
@section('title')
    Housing
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Housing
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Housing</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('miniterminales.create') }}" class="btn-sm btn-primary active" role="button">Agregar Housing</a>
                 <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'miniterminales.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Serial Number', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div> 
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($miniterminales)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Serial</th>
                                        <th>Tipo Housing</th>
                                        <th>Fecha de Instalacion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($miniterminales as $miniterminal)
                                    <tr data-id="{{ $miniterminal->id  }}">
                                        <td>{{ $miniterminal->id }}.</td>
                                        <td>{{ $miniterminal->serialnumber }}</td>
                                        @if ($miniterminal->housing_type_id == 1)
                                            <td>Virtual</td>
                                        @else
                                            <td>Miniterminal</td>
                                        @endif
                                        <td>{{ date('d/m/Y', strtotime($miniterminal->installation_date)) }}</td>
                                        <td>
                                            @if (Sentinel::hasAccess('housing'))
                                                <a class="btn btn-info btn-flat btn-row" title="Dispositivos" href="{{ route('housing.device.index',['housing' => $miniterminal->id ]) }}"><i class="fa fa-building"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('housing.add|edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('miniterminales.edit',['miniterminale' => $miniterminal->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('housing.delete'))
                                                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $miniterminales->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $miniterminales->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
   
    {!! Form::open(['route' => ['miniterminales.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection

@section('page_scripts')
    @include('partials._delete_row_js')
@endsection

