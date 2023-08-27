@extends('layout')
@section('title')
    Redes
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Redes
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Redes</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('owner.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'owner.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($owners)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Nombre</th>
                                    <th>App Last Version</th>
                                    <th style="width:300px">Creado</th>
                                    <th style="width:300px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($owners as $owner)
                                    <tr data-id="{{ $owner->id  }}">
                                        <td>{{ $owner->id }}.</td>
                                        <td>{{ $owner->name }}</td>
                                        <td>{{ $owner->app_last_version }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($owner->created_at)) }}
                                            <i>{{ $owner->createdBy->username }}</i></td>
                                        <td>{{ date('d/m/y H:i', strtotime($owner->updated_at)) }}
                                        <td>
                                            @if (Sentinel::hasAccess('branches'))
                                                <a class="btn btn-info btn-flat btn-row" title="Sucursales" href="{{ route('owner.branches.index',['owner' => $owner->id ]) }}"><i class="fa fa-building"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('owner.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('owner.edit',['owner' => $owner->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('owner.delete'))
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $owners->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $owners->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['owner.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
