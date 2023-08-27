<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
@if(isset($owner) && $owner->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $owner->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($owner->created_at)) }}</p>
    </div>
@endif
@if(isset($owner) && $owner->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $owner->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($owner->updated_at)) }}</p>
    </div>
@endif
<div class="form-group">
    {!! Form::label('app_last_version', 'App Last Version') !!}
    {!! Form::text('app_last_version', null , ['class' => 'form-control', 'placeholder' => 'Última Version de la Aplicación' ]) !!}
</div>
<a class="btn btn-default" href="{{ route('owner.index') }}" role="button">Cancelar</a>
