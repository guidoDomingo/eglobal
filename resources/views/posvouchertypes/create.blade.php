@extends('layout')

@section('title')
Asignar Tipo de Comprobante
@endsection
@section('content')
<section class="content-header">
  <h1>
    Tipo de Comprobante
    <small>Asignar Tipo de Comprobante</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Tipo de Comprobante</a></li>
    <li class="active">agregar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Asignar Tipo de Comprobante</h3>
	    </div>
	    <div class="box-body">
			@include('partials._messages')
			{!! Form::open(['route' => ['pointsofsale.vouchertypes.store', $posId] , 'method' => 'POST', 'role' => 'form']) !!}
			@include('posvouchertypes.partials.fields')
	    </div>
	    <div class="box-footer">
	    	<a class="btn btn-default" href="{{ route('pointsofsale.vouchertypes.index',$posId) }}" role="button">Cancelar</a>
	    	<button type="submit" class="btn btn-primary pull-right">Guardar</button>
	    </div>
	    {!! Form::close() !!}
		</div>

	</div>
</div>
</section>
@endsection
