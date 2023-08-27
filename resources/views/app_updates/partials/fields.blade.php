<div class="form-group">
    {!! Form::label('redes', 'Redes', ['class' => 'col-xs-2']) !!} 
    {!! Form::select('owner_id', $owners, null,['id' => 'owner_id','class' => 'form-control select2', 'style' => 'width: 100%']); !!}
</div>

<div class="form-group">
{!! Form::label('version', 'Versión') !!}
{!! Form::text('version', null , ['class' => 'required form-control', 'placeholder' => 'Ej: 0.1' ]) !!}
</div>

<div class="form-group">
{!! Form::label('file', 'Aplicación') !!}
{!! Form::file('file', null , ['class' => 'required form-control', 'placeholder' => 'Zip de la app' ]) !!}
</div>    
<a class="btn btn-default" href="{{ route('app_updates.index') }}" role="button">Cancelar</a>