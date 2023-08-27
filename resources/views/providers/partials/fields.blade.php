<div class="form-group">
    {!! Form::label('business_name', 'Razón Social') !!}
    {!! Form::text('business_name', null , ['class' => 'form-control', 'placeholder' => 'Razón Social' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('ruc', 'RUC') !!}
    @if(isset($provider))
        {!! Form::text('ruc', null , ['class' => 'form-control', 'placeholder' => 'Registro Único del Contribuyente' ]) !!}
    @else
        {!! Form::text('ruc', null , ['class' => 'form-control', 'placeholder' => 'código de comprobante' ]) !!}
    @endif
</div>
<div class="form-group">
    {!! Form::label('ci', 'Cédula de Identidad') !!}
    {!! Form::text('ci', null , ['class' => 'form-control', 'placeholder' => 'Cédula de Identidad' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('address', 'Dirección') !!}
    {!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'Dirección' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('mobile_phone', 'Teléfono móvil') !!}
    {!! Form::text('mobile_phone', null , ['class' => 'form-control', 'placeholder' => 'Teléfono móvil' ]) !!}
</div>

@if(isset($provider) && $provider->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $provider->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($provider->created_at)) }}</p>
    </div>
@endif
@if(isset($provider) && $provider->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $provider->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($provider->updated_at)) }}</p>
    </div>
@endif
