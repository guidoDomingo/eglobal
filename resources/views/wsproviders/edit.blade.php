@extends('layout')

@section('title')
Proveedor de Servicio Web {{ $wsprovider->name }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $wsprovider->name }}
    <small>Modificación de datos de Proveedor de Servicio Web</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('wsproviders.index') }}">Redes</a></li>
    <li><a href="#">{{ $wsprovider->name }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $wsprovider->name }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($wsprovider, ['route' => ['wsproviders.update', $wsprovider->id ] , 'method' => 'PUT']) !!}
			@include('wsproviders.partials.fields')
			
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>
		<div class="box-footer">
		@include('wsproviders.partials.delete')
		</div>
	</div>
</div>
</section>
@endsection
@section('page_scripts')
@include('partials._delete_form_js')
@endsection
