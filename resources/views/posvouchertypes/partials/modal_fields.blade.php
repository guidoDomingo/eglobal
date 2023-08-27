<div class="form-group">

  {!! Form::label('voucher_type_id', 'Tipo de comprobante') !!}
  {!! Form::select('voucher_type_id',$voucherTypes ,null , ['class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Tipo...','style' => 'width: 100%']) !!}

</div>
<div class="form-group">
{!! Form::label('expedition_point', 'Punto de Expedición') !!}
{!! Form::text('expedition_point', null , ['class' => 'form-control', 'placeholder' => '(xxx)' ]) !!}
</div>