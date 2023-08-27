<div class="form-group">
{!! Form::label('description', 'Nombre') !!}
{!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'nombre de sucursal' ]) !!}
</div>
<div class="form-group">
<div class="form-group">
{!! Form::label('branch_code', 'Código Sucursal (Facturación)') !!}

{!! Form::text('branch_code', null , ['class' => 'form-control', 'placeholder' => 'Código de Sucursal' ]) !!}

</div>
<div class="form-group">
{!! Form::label('address', 'Dirección') !!}
{!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'dirección' ]) !!}
</div>
<div class="form-group">
{!! Form::label('phone', 'Teléfono') !!}
{!! Form::text('phone', null , ['class' => 'form-control', 'placeholder' => 'teléfono' ]) !!}
</div>
<div class="form-group">
	{!! Form::label('user', 'Responsable') !!}
	{!! Form::select('user_id',$users ,$user_id , ['class' => 'form-control select2']) !!}
</div>
<div class="form-group">
	{!! Form::label('executive_id', 'Ejecutivo Responsable') !!}
	{!! Form::select('executive_id',$executives ,$executive_id , ['class' => 'form-control select2']) !!}
</div>
<div class="form-group">
	{!! Form::label('barrio_id', 'Barrio') !!}
	{!! Form::select('barrio_id',$barrios ,$barrio_id , ['class' => 'form-control select2']) !!}
</div>

<div class="form-group">
	{!! Form::label('more_info', 'Horario de atencion') !!}
	{!! Form::text('more_info', null , ['class' => 'form-control', 'placeholder' => 'Horario de atencion' ]) !!}
</div>

@if(isset($branch) && $branch->createdBy != null)
<div class="form-group">
	{!! Form::label('created_by', 'Creado por:') !!}
	<p>{{  $branch->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($branch->created_at)) }}</p>
</div>	
@endif		
@if(isset($branch) && $branch->updatedBy != null)
<div class="form-group">
	{!! Form::label('updated_by', 'Modificado por:') !!}
	<p>{{  $branch->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($branch->updated_at)) }}</p>
</div>
@endif

<a class="btn btn-default" href="{{ route('owner.branches.index',['owner' => $ownerId]) }}" role="button">Cancelar</a>
