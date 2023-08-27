<div class="form-group">
{!! Form::label('name', 'Nombre') !!}
{!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
@if(isset($wsprovider) && $wsprovider->createdBy != null)
<div class="form-group">
	{!! Form::label('created_by', 'Creado por:') !!}
	<p>{{  $wsprovider->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($wsprovider->created_at)) }}</p>
</div>	
@endif		
@if(isset($wsprovider) && $wsprovider->updatedBy != null)
<div class="form-group">
	{!! Form::label('updated_by', 'Modificado por:') !!}
	<p>{{  $wsprovider->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($wsprovider->updated_at)) }}</p>
</div>
@endif

<a class="btn btn-default" href="{{ route('wsproviders.index') }}" role="button">Cancelar</a>
