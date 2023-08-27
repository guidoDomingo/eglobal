<div class="form-group">
@if(isset($voucherTypes))
  {!! Form::label('pos_voucher_type_id', 'Tipo de comprobante') !!}
  {!! Form::select('pos_voucher_type_id',$voucherTypes ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Tipo...']) !!}

  {!! Form::label('expedition_point', 'Punto de Expedición') !!}
@endif

@if(isset($posVoucher))
  <p>{{ $posVoucher->voucherType->expedition_point }}</p>

@endif
</div>

<div class="form-group">
{!! Form::label('stamping', 'Timbrado') !!}
{!! Form::text('stamping', null , ['class' => 'form-control', 'placeholder' => 'Timbrado' ]) !!}
</div>
<div class="form-group">
{!! Form::label('from_number', 'Numeración desde:') !!}
{!! Form::text('from_number', null , ['class' => 'form-control', 'placeholder' => 'Desde' ]) !!}
</div>
<div class="form-group">
{!! Form::label('to_number', 'Numeración hasta:') !!}
{!! Form::text('to_number', null , ['class' => 'form-control', 'placeholder' => 'Hasta' ]) !!}
</div>
<div class="form-group">
{!! Form::label('valid_from', 'Válido desde:') !!}
<div class="input-group">
<div class="input-group-addon">
<i class="fa fa-calendar"></i>
</div>
{!! Form::text('valid_from', null , ['class' => 'form-control datepicker', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
</div><!-- /.input group -->
</div>
{!! Form::label('valid_until', 'Válido hasta:') !!}
<div class="input-group">
<div class="input-group-addon">
<i class="fa fa-calendar"></i>
</div>
{!! Form::text('valid_until', null , ['class' => 'form-control  datepicker', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
</div><!-- /.input group -->
</div>
@include('partials._date_picker')
