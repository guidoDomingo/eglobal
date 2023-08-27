<div class="form-group">
    {!! Form::label('description', 'Nombre') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('ondanet_outcome_code', 'Codigo Ondanet') !!}
    {!! Form::text('ondanet_outcome_code', null , ['class' => 'form-control', 'placeholder' => 'Codigo Ondanet' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('provider_type_code', 'Codigo Proveedor') !!}
    {!! Form::text('provider_type_code', null , ['class' => 'form-control',
    'placeholder' => 'Codigo del proveedor' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('provider_id', 'Proveedor') !!}
    {!! Form::select('provider_id',$providers ,$selected_provider , ['class' => 'form-control',
    'placeholder' => 'Seleccione un Proveedor..']) !!}
</div>

@if (\Sentinel::getUser()->hasRole('superuser') || \Sentinel::getUser()->hasRole('security.admin'))
    <div class="form-group">
        {!! Form::label('owner_id', 'Red:') !!}
        {!! Form::select('owner_id',$owners ,$selected_owner , ['class' => 'form-control',
        'placeholder' => 'Seleccione una Red...']) !!}
    </div>
@endif

@if(isset($outcome) && $outcome->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $outcome->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($outcome->created_at)) }}</p>
    </div>
@endif
@if(isset($outcome) && $outcome->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $outcome->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($outcome->updated_at)) }}</p>
    </div>
@endif
