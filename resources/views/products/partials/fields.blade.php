<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('cost', 'Costo') !!}
    @if(isset($product))
        {!! Form::text('cost', null , ['class' => 'form-control', 'readonly'=>'readonly', 'placeholder' => 'Costo' ]) !!}
    @else
        {!! Form::text('cost', null , ['class' => 'form-control', 'placeholder' => 'Costo' ]) !!}
    @endif
</div>
<div class="form-group">
    {!! Form::label('product_provider_id', 'Proveedor') !!}
    {!! Form::select('product_provider_id',$providers ,$selected_provider , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
</div>
<div class="form-group">
    {!! Form::label('tax_type', 'Tipo IVA') !!}
    @if(isset($product))
        {!! Form::select('tax_type_id',$ondanet_tax_types ,$selected_tax_type , ['class' => 'form-control','readonly' => 'readonly', 'placeholder' => 'Seleccione un Tipo...']) !!}
    @else
        {!! Form::select('tax_type_id',$ondanet_tax_types ,$selected_tax_type , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
    @endif
</div>
<div class="form-group">
    {!! Form::label('currency', 'Moneda') !!}
    @if(isset($product))
        {!! Form::select('currency',$ondanet_currencies ,$selected_currency_type , ['class' => 'form-control','readonly' => 'readonly', 'placeholder' => 'Seleccione un Tipo...']) !!}
    @else
        {!! Form::select('currency',$ondanet_currencies ,$selected_currency_type , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
    @endif
</div>

@if(isset($product) && $product->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $product->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($product->created_at)) }}</p>
    </div>
@endif
@if(isset($product) && $product->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $product->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($product->updated_at)) }}</p>
    </div>
@endif
@if (\Sentinel::getUser()->hasRole('superuser') || \Sentinel::getUser()->hasRole('security.admin'))
    <div class="form-group">
        {!! Form::label('owner_id', 'Red:') !!}
        {!! Form::select('owner_id',$owners ,$selected_owner , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
    </div>
@endif
