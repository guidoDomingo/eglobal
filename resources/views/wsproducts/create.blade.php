@extends('layout')

@section('title')
Nuevo Producto/Operación
@endsection
@section('content')
<section class="content-header">
  <h1>
    Producto/Operación
    <small>Creación de Producto/Operación</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Producto/Operación</a></li>
    <li class="active">agregar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Nuevo Producto/Operación</h3>
	    </div>
	    <div class="box-body">
			@include('partials._messages')
			{!! Form::open(['route' => 'wsproducts.store' , 'method' => 'POST', 'role' => 'form']) !!}
			@include('wsproducts.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>

	</div>
</div>
</section>
@endsection
