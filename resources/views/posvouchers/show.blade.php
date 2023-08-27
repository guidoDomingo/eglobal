@extends('layout')

@section('title')
Datos de Comprobante de {{ $pos->description }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    Datos de Comprobante {{ $posVoucher->voucher_code }}
    <small>Modificación de datos de Tipo de Comprobante</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('pos.index') }}"> Puntos de Venta</a>
    <li><a href="#">Comprobante</a></li>
    <li class="active">ver</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
			<div class="box-header with-border">
		      <h3 class="box-title">Datos de Comprobante</h3>
		    </div>
		    <div class="box-body">
		    	<div class="form-group">
				<p><strong>Tipo de Comprobante: </strong>{{ $posVoucherTypeDesc }}</p>
				<p><strong>Punto de expedición: </strong>{{ $posVoucherType->voucherType->expedition_point }}</p>
				<p><strong>Código de comprobante: </strong>{{ $posVoucher->voucher_code }}</p>
				<p><strong>Timbrado: </strong>{{ $posVoucher->stamping }}</p>
				<p><strong>Numeración Desde: </strong>{{ $posVoucher->from_number }}</p>
				<p><strong>Numeración Hasta: </strong>{{ $posVoucher->to_number }}</p>
				<p><strong>Válido desde: </strong>{{ $posVoucher->valid_from }}</p>
				<p><strong>Válido hasta: </strong>{{ $posVoucher->valid_until }}</p>

		    	</div>
				 <div class="box-footer">
			    	<a class="btn btn-default" href="{{ route('pointsofsale.vouchers.index',$posId) }}" role="button">Volver</a>
			    </div>
			</div>
		</div>
	</div>
</div>
</div>
</section>
@endsection
@section('page_scripts')
@endsection
