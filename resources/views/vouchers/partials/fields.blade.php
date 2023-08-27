<div class="form-group">
    {!! Form::label('description', 'Nombre') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('voucher_type_code', 'Código de Comprobante') !!}
    @if(isset($vouchertype))
        {!! Form::text('voucher_type_code', null , ['class' => 'form-control', 'readonly' => 'readonly','placeholder' => 'código de comprobante' ]) !!}
    @else
        {!! Form::text('voucher_type_code', null , ['class' => 'form-control', 'placeholder' => 'código de comprobante' ]) !!}
    @endif
</div>
@if(isset($vouchertype) && $vouchertype->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $vouchertype->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($vouchertype->created_at)) }}</p>
    </div>
@endif
@if(isset($vouchertype) && $vouchertype->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $vouchertype->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($vouchertype->updated_at)) }}</p>
    </div>
@endif
