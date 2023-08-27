@extends('layout')
@section('title')
Web Services
@endsection
@section('content')
<section class="content-header">
  <h1>
    Web Services
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Web Services</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('webservices.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => 'webservices.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
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
              <th>Proveedor</th>
              <th style="width:200px">Modificado</th>
              <th style="width:160px">Estado</th>
              <th style="width:200px">Acciones</th>
            </tr>
          </thead>
          @foreach($webservices as $webservice)
          <tr data-id="{{ $webservice->id  }}">
            <td>{{ $webservice->id }}.</td>
            <td>{{ $webservice->name }}</td>
            <td>{{ $webservice->webserviceprovider->name }}</td>
            <td>{{ date('d/m/y H:i', strtotime($webservice->updated_at)) }} @if($webservice->updatedBy != null) - <i>{{ $webservice->createdBy->username }}</i> @endif </td>
            <td>
              @if($webservice->status == 0)
                <span><i class="fa fa-circle text-green"></i> Online</span> - <a href="{{ route('services_status', ['id' => $webservice->id, 'value' => 1]) }}" class="label label-danger userStatus" id="">
                  Desactivar
                </a>
              @else
                <span><i class="fa fa-circle text-gray"></i> Offline</span> - <a href="{{ route('services_status', ['id' => $webservice->id, 'value' => 0]) }}" class="label label-success userStatus" id="">
                  Activar
                </a>
              @endif
            </td>
            <td>
              @if (Sentinel::hasAnyAccess('webservices.add|edit'))
              <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('webservices.edit',$webservice->id)}}"><i class="fa fa-pencil"></i></a>
              @endif
              @if (Sentinel::hasAnyAccess('webservices.delete'))
              <a class="btn btn-danger btn-flat btn-row btn-delete" title="Eliminar" href="#" class="btn-delete"><i class="fa fa-remove"></i> </a>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $webservices->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $webservices->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>
{!! Form::open(['route' => ['webservices.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}

@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
