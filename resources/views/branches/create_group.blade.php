@extends('layout')

@section('title')
Agregar Sucursal
@endsection
@section('content')
<section class="content-header">
  <h1>
    Sucursal
    <small>Agregar Sucursal</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Sucursal</a></li>
    <li class="active">agregar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Nueva Sucursal para el grupo</h3>
	    </div>
	    <div class="box-body">
			@include('partials._messages')
			{!! Form::open(['route' => ['groups.branches.store', $groupId] , 'method' => 'POST', 'role' => 'form']) !!}
			@include('branches.partials.field_group')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>

	</div>
</div>
</section>
@endsection
