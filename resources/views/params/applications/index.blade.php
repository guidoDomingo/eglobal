@extends('layout')
@section('title')
Configuración de Aplicación
@endsection
@section('content')
<section class="content-header">
  <h1>
    Configuración de Aplicación
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
    <li><a href="#">Configuración de Aplicación</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('applications.params.create', $appId) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => ['applications.params.index', $appId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
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
              <th>Descripción</th>
              <th>Valor</th>
              <th style="width:150px">Creado</th>
              <th style="width:150px">Modificado</th>
              <th style="width:250px">Acciones</th>
            </tr>
          </thead>
          @foreach($appconfigs as $param)
            <tr data-id="{{ $param->id  }}">
              <td>{{ $param->id }}.</td>
              <td> {{ $param->description }} </td>
              <td> {{ $param->value }} </td>
              <td> {{ date('d/m/y H:i', strtotime($param->created_at)) }} </td>
              <td> {{ date('d/m/y H:i', strtotime($param->updated_at)) }} </td>
              <td>
                @if (Sentinel::hasAccess('applications.params.add|edit'))
                    <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('applications.params.edit',[$param->application_id, $param->param_id ]) }}"><i class="fa fa-pencil"></i> </a>
                @endif

                @if (Sentinel::hasAccess('applications.params.delete'))
                    <a href="" class="btn btn-danger btn-flat btn-row btn-delete" title="Eliminar" ><i class="fa fa-remove"></i></a>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $appconfigs->total() }}
      registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $appconfigs->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>

{!! Form::open(['route' => ['applications.params.destroy', $appId,':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}


@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
