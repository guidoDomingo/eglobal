<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::text('description', !is_null($permission->description) ? $permission->description : null , ['class' => 'form-control', 'placeholder' => 'Descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('permission', 'Permiso') !!}
    {!! Form::text('permission', !is_null($permission->permission) ? $permission->permission : null , ['class' => 'form-control', 'placeholder' => 'Nombre de ruta' ]) !!}
</div>
<a class="btn btn-default" href="{{ route('permissions.index') }}" role="button">Cancelar</a>
