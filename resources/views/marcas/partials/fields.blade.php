<div class="form-group">
    {!! Form::label('descripcion', 'Descripción') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('imagen_asociada', 'Imagen Asociada') !!}
    <input type="file" class="filepond" name="imagen_asociada" data-max-file-size="3MB" data-max-files="3">
    @if(isset($marca))
        <small style="">Nota: cargar una imagen solo en caso de querer modificar la imagen actual</small>
    @endif
</div>
<div class="form-group">
    {!! Form::label('categoria', 'Categoria') !!}
    {!! Form::select('categoria_id', $categorias, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('service_source', 'Service Source') !!}
    {!! Form::select('service_source_id', $service_sources, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
@if(isset($marca) && $marca->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($marca->created_at)) }}</p>
    </div>
@endif
@if(isset($marca) && $marca->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($marca->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('marca.index') }}" role="button">Cancelar</a>
