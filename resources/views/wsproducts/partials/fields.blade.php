<div class="form-group">
@if(isset($wsproviders))
  {!! Form::label('service_provider_id', 'Proveedor') !!}
  {!! Form::select('service_provider_id',$wsproviders ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Proveedor...']) !!}
@endif
</div>
<div class="form-group">
{!! Form::label('description', 'Descripción') !!}
{!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Descripción' ]) !!}
</div>

@if(isset($wsproduct) && $wsproduct->createdBy != null)
<div class="form-group">
	{!! Form::label('created_by', 'Creado por:') !!}
	<p>{{  $wsproduct->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($wsproduct->created_at)) }}</p>
</div>	
@endif		
@if(isset($wsproduct) && $wsproduct->updatedBy != null)
<div class="form-group">
	{!! Form::label('updated_by', 'Modificado por:') !!}
	<p>{{  $wsproduct->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($wsproduct->updated_at)) }}</p>
</div>
@endif
<a class="btn btn-default" href="{{ route('wsproducts.index') }}" role="button">Cancelar</a>
