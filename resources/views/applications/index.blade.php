@extends('layout')
@section('title')
    Aplicaciones
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Aplicaciones
            <small>Listado de Aplicaciones</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('applications.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'applications.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close()!!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-striped">
                            <tbody><thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Nombre</th>
                                <th>Version Name</th>
                                <th>Version Code</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:100px">Acciones</th>
                                <th style="width:142px">Adminstrar</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($applications as $application)
                                <tr data-id="{{ $application->id  }}">
                                    <td>{{ $application->id }}.</td>
                                    <td>{{ $application->name }}</td>
                                    <td>{{ $application->version_name }}</td>
                                    <td>{{ $application->version_code }}</td>
                                    <td>{{ $application->created_at }}</td>
                                    <td>{{ $application->updated_at }}</td>
                                    <td>
                                        @if (Sentinel::hasAccess('applications.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('applications.edit',$application)}}"><i class="fa fa-pencil"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.delete'))
                                            <a class=" btn-delete btn btn-danger btn-flat btn-row" href="#"><i class="fa fa-remove" title="Eliminar"></i></a>
                                        @endif
                                    </td>
                                    <td>
                                        @if (Sentinel::hasAccess('applications.screens'))
                                         <a class="btn btn-info btn-flat btn-row"  href="{{ route('applications.screens.index',['application' => $application->id]) }}"><i class="fa fa-object-group" title="Pantallas"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.versions'))
                                         <a class="btn btn-warning btn-flat btn-row" href="{{ route('applications.versions.index',['application' => $application->id ]) }}"><i class="fa fa-cube" title="Configuración de servicios"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.params'))
                                        <a class="btn btn-primary btn-flat btn-row" href="{{ route('applications.params.index',['application' => $application->id ]) }}"><i class="fa fa-gear" title="Parámetros de la aplicación"></i></a>
                                        @endif
                                    </td>
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
         <div class="dataTables_info" role="status" aria-live="polite">{{ $applications->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $applications->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {!! Form::open(['route' => ['applications.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
