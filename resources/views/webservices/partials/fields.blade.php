<div class="form-group">
@if(isset($wsproviders))
  {!! Form::label('service_provider_id', 'Proveedor') !!}
  {!! Form::select('service_provider_id',$wsproviders ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Proveedor...']) !!}
@endif
</div>
<div class="form-group">
	@if(isset($app_categories))
		{!! Form::label('app_categories', 'Categoría') !!}
		{!! Form::select('app_categories_id',$app_categories ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione una Categoría ...']) !!}
	@endif
</div>
<div class="form-group">
{!! Form::label('name', 'Nombre') !!}
{!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
{!! Form::label('api_prefix', 'Prefijo API') !!}
{!! Form::text('api_prefix', null , ['class' => 'form-control', 'placeholder' => 'ejemplo/v1/' ]) !!}
</div>
<div class="form-group">
{!! Form::label('url', 'Url del Servicio (REST, SOAP)') !!}
{!! Form::text('url', null , ['class' => 'form-control', 'placeholder' => 'http://www.ejemplo.com/api/v1/' ]) !!}
</div>
<div class="form-group">
{!! Form::label('ip_address', 'IP del Servicio') !!}
{!! Form::text('ip_address', null , ['class' => 'form-control', 'placeholder' => 'xxx.xxx.xxx.xxx' ]) !!}
</div>
<div class="form-group">
{!! Form::label('port', 'Puerto del Servicio') !!}
{!! Form::text('port', null , ['class' => 'form-control', 'placeholder' => '80' ]) !!}
</div>
<div class="form-group">
	{!! Form::label('user', 'Usuario') !!}
	{!! Form::text('user_name', null , ['class' => 'form-control', 'placeholder' => 'Usuario' ]) !!}
</div>
<div class="form-group">
	{!! Form::label('password', 'Clave') !!}
	{!! Form::text('password', null , ['class' => 'form-control', 'placeholder' => 'Clave' ]) !!}
</div>
<div class="form-group">
{!! Form::label('api_key', 'API Key del Servicio') !!}
{!! Form::text('api_key', null , ['class' => 'form-control', 'placeholder' => 'API Key' ]) !!}
</div>
@if(isset($webservice) && $webservice->createdBy != null)
<div class="form-group">
	{!! Form::label('created_by', 'Creado por:') !!}
	<p>{{  $webservice->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($webservice->created_at)) }}</p>
</div>	
@endif		
@if(isset($webservice) && $webservice->updatedBy != null)
<div class="form-group">
	{!! Form::label('updated_by', 'Modificado por:') !!}
	<p>{{  $webservice->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($webservice->updated_at)) }}</p>
</div>
@endif

