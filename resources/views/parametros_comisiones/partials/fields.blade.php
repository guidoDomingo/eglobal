<div class="form-group">
    {!! Form::label('owner', 'Red') !!}
    {!! Form::select('owner_id', $owners, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'id' => 'owner_id']) !!}
    <small><i class="fa fa-info"></i> Si selecciona una red, los cambios afectaran a todos los atms que pertenezcan a la misma</small>
</div>
<div class="form-group">
    {!! Form::label('atm', 'Atm') !!}
    {!! Form::select('atm_id', $atms, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'id' => 'atm_id']) !!}
</div>
<div class="form-group">
    {!! Form::label('tipo_servicio', 'Tipo de Servicio') !!}
    {!! Form::select('tipo_servicio_id', $tipo_servicio, null, ['id' => 'tipo_servicio_id','class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>

<div class="proveedores" @if(isset($parametro_comision) && $parametro_comision->tipo_servicio_id == 1) style="display: none" @endif>
    <div class="form-group">
        {!! Form::label('service_source', 'Service Sources') !!}
        {!! Form::select('service_source_id', $service_sources, null, ['id' => 'service_source_id','class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'style' => 'width:100%']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('service_id', 'Servicio') !!}
    @if(isset($parametro_comision))
    {!! Form::select('service_id', ($parametro_comision->tipo_servicio_id == 0) ? $servicios : $servicios_propios, $parametro_comision->service_id , ['id' => 'service_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione el servicio' ]) !!}
    @else
    {!! Form::select('service_id', [], null , ['id' => 'service_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione el servicio' ]) !!}
    @endif
</div>

<div class="form-group">
    {!! Form::label('comision', 'Porcentaje Comisión') !!}
    {!! Form::text('comision', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el porcentaje de comisión' ]) !!}
</div>

@if(isset($parametro_comision) && $parametro_comision->created_byy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($parametro_comision->created_at)) }}</p>
    </div>
@endif
@if(isset($parametro_comision) && $parametro_comision->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($parametro_comision->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('parametros_comisiones.index') }}" role="button">Cancelar</a>
