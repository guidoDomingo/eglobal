<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('departamento', 'Departamento') !!}
    {!! Form::select('departamento_id', $departamentos, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
@if(isset($ciudad))
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($ciudad->created_at)) }}</p>
    </div>
@endif
@if(isset($ciudad))
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($ciudad->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('ciudades.index') }}" role="button">Cancelar</a>
