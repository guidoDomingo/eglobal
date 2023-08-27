@extends('layout')
@section('title')
Tipos de Comprobantes
@endsection
@section('content')
<section class="content-header">
  <h1>
    Tipos de Comprobantes
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('pos.index') }}"> Puntos de Venta</a>
    </li>
    <li><a href="#">Tipos de Comprobantes</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('pointsofsale.vouchertypes.create', $posId) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => ['pointsofsale.vouchertypes.index', $posId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
          {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Punto de expedición', 'autocomplete' => 'off' ]) !!}
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
              <th>Punto de Expedición</th>
              <th>Tipo de Comprobante</th>
              <th style="width:300px">Creado</th>
              <th style="width:300px">Modificado</th>
              <th style="width:250px">Acciones</th>
            </tr>
          </thead>
          @foreach($voucherType as $pos)
          <tr data-id="{{ $pos->id  }}">
            <td>{{ $pos->id }}.</td>
            <td>{{ $pos->expedition_point }}</td>
            <td>{{ $pos->voucherType->description }}</td>
            <td>{{ date('d/m/y H:i', strtotime($pos->created_at)) }} - <i>{{ $pos->createdBy->username }}</i></td>
            <td>{{ date('d/m/y H:i', strtotime($pos->updated_at)) }} @if($pos->updatedBy != null) - <i>{{ $pos->createdBy->username }}</i> @endif </td>
            <td>
              @if (Sentinel::hasAnyAccess('vouchers.add|edit'))
                <a class="btn btn-success btn-flat btn-row" title='Editar' href="{{ route('pointsofsale.vouchertypes.edit',[$posId, $pos])}}"><i class="fa fa-pencil"></i></a>
              @endif
              @if (Sentinel::hasAnyAccess('vouchers.delete'))
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $voucherType->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $voucherType->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>

{!! Form::open(['route' => ['pointsofsale.vouchertypes.destroy',$posId, ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}


@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
