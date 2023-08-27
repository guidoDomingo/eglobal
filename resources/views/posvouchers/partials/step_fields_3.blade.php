<div class="form-group">
  {!! Form::label('pos_voucher_type_id', 'Tipo de comprobante') !!} <a style="margin-left: 8em" href='#' id="nuevoTipoComprobante" data-toggle="modal" data-target="#modalNuevoTipo"><small>Asignar <i class="fa fa-plus"></i></small></a>
	<div class="input-group">
    	<div class="input-group-addon">
            <i class="fa fa-file-text"></i>
        </div>  
		@if(isset($posVoucher))
			@if(empty($posVoucher))
				{!! Form::select('pos_voucher_type_id', [] ,null , ['class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Tipo...','style' => 'width: 100%']) !!}
			@else
		
				{!! Form::select('pos_voucher_type_id', [$posVoucher->voucherType->id => $posVoucher->voucherType->getDescription().' - '.$posVoucher->voucher_code], $posVoucher->voucherType->id, ['class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Tipo...','style' => 'width: 100%']) !!}
			@endif
		@else
			{!! Form::select('pos_voucher_type_id', [] ,null , ['class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Tipo...','style' => 'width: 100%']) !!}
		@endif
	</div>
</div>

<div class="form-group">
	{!! Form::label('stamping', 'Timbrado') !!}
	<div class="input-group">
    	<div class="input-group-addon">
            <i class="fa fa-keyboard-o"></i>
        </div>  
		{!! Form::text('stamping', null , ['class' => 'form-control', 'placeholder' => 'Timbrado' ]) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label('from_number', 'Numeración desde:') !!}
	<div class="input-group">
    	<div class="input-group-addon">
            <i class="fa fa-filter"></i>
        </div>  
		{!! Form::text('from_number', null , ['class' => 'form-control', 'placeholder' => 'Desde' ]) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label('to_number', 'Numeración hasta:') !!}
	<div class="input-group">
    	<div class="input-group-addon">
            <i class="fa fa-filter"></i>
        </div>  
		{!! Form::text('to_number', null , ['class' => 'form-control', 'placeholder' => 'Hasta' ]) !!}
	</div>
</div>
<div class="form-group">
	{!! Form::label('valid_from', 'Válido desde:') !!}
	<div class="input-group">
		<div class="input-group-addon">
			<i class="fa fa-calendar"></i>
		</div>
		{!! Form::text('valid_from', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
	</div><!-- /.input group -->
</div>
{!! Form::label('valid_until', 'Válido hasta:') !!}
<div class="input-group">
	<div class="input-group-addon">
		<i class="fa fa-calendar"></i>
	</div>
	{!! Form::text('valid_until', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
</div><!-- /.input group -->
@include('partials._date_picker')
