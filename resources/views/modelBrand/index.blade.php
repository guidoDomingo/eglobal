@extends('layout')
@section('title')
Modelos
@endsection
@section('content')
<section class="content-header">
  <h1>
    Modelos
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Modelos</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
      <a href="{{ route('model.brand.create',['model' => $brandId]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <a href="{{ route('brands.index') }}" class="btn-sm btn-default active" role="button">Volver</a><p>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => ['model.brand.index',$brandId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
          {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Modelo', 'autocomplete' => 'off' ]) !!}
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
              <th style="width:10px">#ID</th>
              <th style="text-align:center;">Descripción</th>
              <th style="text-align:center;">Fecha de Creación</th>      
              <th style="text-align:center;">Prioritario</th>              
              <th style="width:200px; text-align:center;">Acciones</th>
            </tr>
          </thead>
          @foreach($modelos as $modelo)
          <tr data-id="{{ $modelo->id  }}">
            <td>{{ $modelo->id }}.</td>
            <td>{{ $modelo->description }}</td>
            <td style="text-align:center;">{{ date('d/m/y H:i', strtotime($modelo->created_at)) }} </td>
            @if ($modelo->priority == 1)
                <td style="text-align:center;">Sí</td>
            @else
                <td style="text-align:center;">No</td>
            @endif

            <td style="text-align:center;">
              @if (Sentinel::hasAccess('model.add|edit'))
                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('model.brand.edit',[$brandId,$modelo])}}"><i class="fa fa-pencil"></i></a>
              @endif
              @if (Sentinel::hasAccess('model.delete'))
                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i></a>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $modelos->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $modelos->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>

{!! Form::open(['route' => ['model.brand.destroy',$brandId,':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}


@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection