@extends('layout')

@section('title')
Producto/Operación {{ $wsproduct->name }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $wsproduct->description }}
    <small>Modificación de datos de Producto/Operación</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('wsproducts.index') }}">Productos/Operaciones</a></li>
    <li><a href="#">{{ $wsproduct->name }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-6">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $wsproduct->name }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($wsproduct, ['route' => ['wsproducts.update',$wsproduct->id ] , 'method' => 'PUT']) !!}
			@include('wsproducts.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>
	</div>
	<div class="col-md-6">
		@include('wsproducts.partials.requests.table')
	</div>
	<div class="col-md-6">
		@include('wsproducts.partials.products.table')
	</div>

</div>
</section>
@endsection
@section('page_scripts')
@include('partials._delete_form_js')
@endsection
