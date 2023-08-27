@extends('layout')

@section('title')
Dispositivo {{ $device->serialnumber }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $device->description }}
    <small>Modificación de datos del dispositivo</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Dispositivo</a></li>
	<li><a href="#">{{ $device->serialnumber }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $device->serialnumber }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($device, ['route' => ['housing.device.update',$device->housing_id,$device->id ] , 'method' => 'PUT']) !!}
			@include('devices.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>
		<div class="box-footer">
		
		</div>
	</div>
</div>
</section>
@endsection
@section('page_scripts')
@include('partials._delete_form_js')
@endsection
