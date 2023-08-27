<div class="form-group">
    {!! Form::label('marca', 'Marca') !!}
    {!! Form::select('marca_id', $marcas, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('service_source', 'Service Source') !!}
    {!! Form::select('service_source_id', $service_sources, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('imagen_asociada', 'Imagen Asociada') !!}
    <input type="file" class="filepond" name="imagen_asociada" data-max-file-size="3MB" data-max-files="3">
    @if(isset($servicio_marca))
        <small style="">Nota: cargar una imagen solo en caso de querer modificar la imagen actual</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('service', 'Service ID') !!}
    {!! Form::text('service_id', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el service ID' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('nivel', 'Nivel') !!}
    {!! Form::text('nivel', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nivel' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('ondanet_code', 'Cód. Ondanet') !!}
    {!! Form::text('ondanet_code', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el cód. ondanet' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('promedio_comision', 'Promedio Comisión') !!}
    {!! Form::text('promedio_comision', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el promedio comisión' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('tipo', 'Tipo') !!}
    {!! Form::text('tipo', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el tipo' ]) !!}
</div>
@if(isset($servicio_marca) && $servicio_marca->created_at != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($servicio_marca->created_at)) }}</p>
    </div>
@endif
@if(isset($servicio_marca) && $servicio_marca->updated_at != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($servicio_marca->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('servicios_marca.index') }}" role="button">Cancelar</a>
