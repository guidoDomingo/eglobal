<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' , 'id'=>'descripcion_barrio']) !!}
</div>
<div class="form-group">
    {!! Form::label('ciudad', 'Ciudad') !!}
    {!! Form::select('ciudad_id', $ciudades, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
@if(isset($barrio))
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($barrio->created_at)) }}</p>
    </div>
@endif
@if(isset($barrio))
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($barrio->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('barrios.index') }}" role="button">Cancelar</a>
