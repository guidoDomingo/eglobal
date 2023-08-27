@extends('layout')
@section('title')
Versiones
@endsection
@section('content')
<section class="content-header">
  <h1>
    Versiones
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Versiones</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">
    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('applications.versions.create',['app_id' => $appId ]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => ['applications.versions.index', $appId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
          {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
          {!! Form::close() !!}
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
                        <th>Pantalla Principal</th>
                        <th>Creado</th>
                        <th data-width="16" class="hidden-xs hidden-sm">Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($versiones as $version)
                    <tr data-id="{{ $version->id  }}">
                      <td>{{ $version->id }}</td>
                      <td>{{ $version->name}}</td>
                      <td>{{ $version->screen_name}}</td>
                      <td>{{ $version->created_at }}</td>
                      @if($version->id == $current_app->current_version)
                        <td>Activo</td>
                      @else
                            <td>Inactivo - <a href="{{ route('app_current_version', ['version_id' => $version->id, 'app_id' => $current_app->id]) }}" class="label label-success userStatus" id="{{$version->id}}">
                                    Activar
                                </a></td>
                      @endif
                        <td>
                            @if (Sentinel::hasAccess('applications.versions.add|edit'))
                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ url('/applications/'.$appId.'/versions/'.$version->id.'/edit')}}"><i class="fa fa-pencil"></i> </a>
                            @endif
                            @if (Sentinel::hasAccess('applications.versions.delete'))
                                <a class="btn btn-danger btn-flat btn-row btn-delete" title="Eliminar" href="#"> <i class="fa fa-remove"></i> </a>
                            @endif
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
                  <div class="dataTables_info" role="status" aria-live="polite">{{ $versiones->total() }} registros en total</div>
              </div>
              <div class="col-sm-7">
                  <div class="dataTables_paginate paging_simple_numbers">
                      {!! $versiones->appends(request()->input())->render()  !!}
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</section>

{!! Form::open(['route' => ['applications.versions.destroy', $appId,':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}

@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
