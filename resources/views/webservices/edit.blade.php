@extends('layout')

@section('title')
Web Service {{ $webservice->name }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $webservice->description }}
    <small>Modificación de datos de Web Service</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('webservices.index') }}">Web Services</a></li>
    <li><a href="#">{{ $webservice->name }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-6">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $webservice->name }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($webservice, ['route' => ['webservices.update',$webservice->id ] , 'method' => 'PUT']) !!}
			@include('webservices.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>
	</div>
	<div class="col-md-6">
		@include('webservices.partials.requests.form')
		
	</div>
	<div class="col-md-12">
	@include('webservices.partials.requests.table')
	</div>
</div>
</section>
@endsection
@section('page_scripts')
@include('partials._delete_form_js')
@endsection
