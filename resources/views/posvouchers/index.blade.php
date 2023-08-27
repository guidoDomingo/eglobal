@extends('layout')
@section('title')
Comprobantes de Punto de Venta
@endsection
@section('content')
<section class="content-header">
  <h1>
    Comprobantes de Punto de Venta
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Comprobantes</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('pointsofsale.vouchers.create', $posId) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => ['pointsofsale.vouchers.index', $posId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
          {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Código', 'autocomplete' => 'off' ]) !!}
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
              <th style="width:150px">Tipo</th>
              <th>Timbrado</th>
              <th style="width:100px">Válido Desde</th>
              <th style="width:100px">Válido Hasta</th>
              <th style="width:300px">Creado</th>
              <th style="width:300px">Modificado</th>
              <th style="width:250px">Acciones</th>
            </tr>
          </thead>
          @foreach($voucherType as $pos)
          <tr data-id="{{ $pos->id  }}">
            <td>{{ $pos->id }}.</td>
            <td>{{ $pos->VoucherType->VoucherType->description }}</td>
            <td>{{ $pos->stamping }}</td>
            <td>{{ $pos->valid_from }}</td>
            <td>{{ $pos->valid_until }}</td>
            <td>{{ $pos->created_at }} - <i>{{ $pos->createdBy->username }}</i></td>
            <td>{{ $pos->updated_at }} @if($pos->updatedBy != null) - <i>{{ $pos->createdBy->username }}</i> @endif </td>
            <td>
              @if (Sentinel::hasAnyAccess('vouchers.add|edit'))
               <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('pointsofsale.vouchers.edit',[$posId, $pos])}}"><i class="fa fa-pencil"></i> </a>
              @endif
              @if (Sentinel::hasAnyAccess('vouchers.add|edit'))
               <a class="btn btn-info btn-flat btn-row" title="Ver" href="{{ route('pointsofsale.vouchers.show',[$posId, $pos])}}"><i class="fa fa-search"></i></a>
              @endif
              @if (Sentinel::hasAnyAccess('vouchers.delete'))
               <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" class=""><i class="fa fa-remove"></i></a>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $voucherType->count() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $voucherType->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>

{!! Form::open(['route' => ['pointsofsale.vouchers.destroy',$posId, ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}


@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
