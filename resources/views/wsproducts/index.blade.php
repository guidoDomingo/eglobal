@extends('layout')
@section('title')
Productos/Operaciones de Webservices
@endsection
@section('content')
<section class="content-header">
  <h1>
    Productos/Operaciones de Webservices
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Productos/Operaciones de Webservices</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('wsproducts.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['name']),['route' => 'wsproducts.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
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
              <th style="width:200px">Creado</th>
              <!-- <th style="width:200px">Modificado</th> -->
              <th style="width:250px">Acciones</th>
            </tr>
          </thead>
          @foreach($wsproducts as $product)

            <tr data-id="{{ $product->id  }}">
              <td>{{ $product->id }}.</td>
              <td>{{ $product->description }}</td>
              <td>{{ $product->webserviceprovider->name }}</td>
              <td>{{ date('d/m/y H:i', strtotime($product->created_at)) }} - <i>{{ $product->createdBy->username }}</i></td>
            <!-- <td>{{ date('d/m/y H:i', strtotime($product->updated_at)) }} @if($product->updatedBy != null) - <i>{{ $product->createdBy->username }}</i> @endif </td>-->
              <td>
                <a class="btn btn-info btn-flat btn-row" title="Flujo" href="{{ route('wsproducts.wsbuilder.index',$product->id)}}"><i class="fa fa-desktop"></i> </a>
                <a class="btn btn-warning btn-flat btn-row" title="Modelos" href="{{ route('wsproducts.models.index',$product->id)}}"><i class="fa fa-gear"></i> </a>
                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('wsproducts.edit',$product->id)}}"><i class="fa fa-pencil"></i> </a>
                <a class="btn btn-danger btn-flat btn-row btn-delete" title="Eliminar" href="#"><i class="fa fa-remove"></i> </a>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $wsproducts->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $wsproducts->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>
{!! Form::open(['route' => ['wsproducts.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}

@endsection
@section('page_scripts')
@include('partials._delete_row_js')
@endsection
