@extends('layout')

@section('title')
Nuevo comprobante para Punto de Venta
@endsection
@section('content')
<section class="content-header">
  <h1>
    Comprobante
    <small>Nuevo Comprobante</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('pos.index') }}"> Puntos de Venta</a>
    <li><a href="#">Nuevo comprobante</a></li>
    <li class="active">agregar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Nuevo Comprobante</h3>
	    </div>
	    <div class="box-body">
			@include('partials._messages')
			{!! Form::open(['route' => ['pointsofsale.vouchers.store', $posId] , 'method' => 'POST', 'role' => 'form']) !!}
			@include('posvouchers.partials.fields')
	    </div>
	    <div class="box-footer">
	    	<a class="btn btn-default" href="{{ route('pointsofsale.vouchers.index',$posId) }}" role="button">Cancelar</a>
	    	<button type="submit" class="btn btn-primary pull-right">Guardar</button>
	    </div>
	    {!! Form::close() !!}
		</div>

	</div>
</div>
</section>
@endsection
