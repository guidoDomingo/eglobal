<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' , 'id' => 'descripcion_departamento']) !!}
</div>

@if(isset($departamento) && $departamento->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($departamento->created_at)) }}</p>
    </div>
@endif
@if(isset($departamento) && $departamento->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($departamento->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('departamentos.index') }}" role="button">Cancelar</a>
