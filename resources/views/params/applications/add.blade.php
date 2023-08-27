@extends('layout')

@section('title')
Asociar Parámetro a {{ $application->name }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    Parámetro de Aplicación
    <small>Asociar Parámetro</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
    <li><a href="#">Parámetros</a></li>
    <li class="active">asociar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Asociar Parámetro a {{ $application->name }}</h3>
	    </div>
	    <div class="box-body">
		@include('partials._messages')
		{!! Form::open(['route' => ['applications.params.store', $application] , 'method' => 'POST', 'role' => 'form']) !!}
			@include('params.applications.partials.fields')
	    </div>
	    <div class="box-footer">
	    	<a class="btn btn-default" href="{{ route('applications.params.index',$application)}}" role="button">Cancelar</a>
	    	<button type="submit" class="btn btn-primary pull-right">Guardar</button>
	    </div>
	    {!! Form::close() !!}
		</div>

	</div>
</div>
</section>
@endsection
