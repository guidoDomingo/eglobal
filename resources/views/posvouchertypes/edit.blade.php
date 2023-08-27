@extends('layout')

@section('title')
Tipo de Comprobante {{ $voucherType->description }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $voucherType->description }}
    <small>Modificación de datos de Tipo de Comprobante</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('pos.index') }}">Puntos de Ventas</a></li>
    <li><a href="#">{{ $voucherType->description }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $voucherType->description }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($posVoucherType, ['route' => ['pointsofsale.vouchertypes.update', $posVoucherType->point_of_sale_id, $posVoucherType->id ] , 'method' => 'PUT']) !!}
			@include('posvouchertypes.partials.fields')
			
	    </div>
		 <div class="box-footer">
	    	<a class="btn btn-default" href="{{ route('pointsofsale.vouchertypes.index',$posId) }}" role="button">Cancelar</a>
	    	<button type="submit" class="btn btn-primary pull-right">Guardar</button>
	    	
	    </div>
		</div>
	    {!! Form::close() !!}
		</div>
	</div>
</div>
</section>
@endsection
@section('page_scripts')
@include('partials._delete_form_js')
@endsection
