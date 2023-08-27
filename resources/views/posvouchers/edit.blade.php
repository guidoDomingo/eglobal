@extends('layout')

@section('title')
Comprobante de {{ $pos->description }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    Comprobante {{ $posVoucher->voucher_code }}
    <small>Modificación de datos de Tipo de Comprobante</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('pos.index') }}"> Puntos de Venta</a>
    <li><a href="#">Comprobantes</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar Comprobante {{ $posVoucher->voucher_code }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($posVoucher, ['route' => ['pointsofsale.vouchers.update', $posVoucher->point_of_sale_id,$posVoucher->id] , 'method' => 'PUT']) !!}
			@include('posvouchers.partials.fields')
			
	    </div>
		 <div class="box-footer">
	    	<a class="btn btn-default" href="{{ route('pointsofsale.vouchers.index',$posId) }}" role="button">Cancelar</a>
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
