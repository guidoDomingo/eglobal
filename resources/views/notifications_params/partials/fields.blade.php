
<div class="form-group">
    {!! Form::label('prefix', 'Alerta') !!}
    <p>
        {{ $notifications_params->prefix }}
    </p>
</div>
<div class="form-group">
    {!! Form::label('prefix', 'Tipo de Notificación') !!}
    <p>
        {{ $tipo_notificacion->description }}
    </p>
</div>
<div class="form-group">
    {!! Form::label('service_source', 'Service Source') !!}
    {!! Form::select('service_source_id', $service_sources, null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'id' => 'serviceSourceId']) !!}
</div>
<div class="form-group">
    <label>Servicios</label>
    {!! Form::select('service_id[]', $servicios, $servicios_guardados, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'service_id',  'style' => 'width:100%', 'data-placeholder' => 'Elija los servicios', 'id' => 'serviceId']) !!}
</div>
<div class="form-group">
    {!! Form::label('valor', 'Valor') !!}
    {!! Form::text('valor', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el valor' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('mensaje', 'Mensaje') !!}
    {!! Form::text('mensaje', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el cód. ondanet' ]) !!}
    <small style="">Nota: no modificar los parametros que vengan dentro de los símbolos <strong>{ }</strong></small>
</div>
<div class="form-group">
    {!! Form::label('destinatarios', 'Destinatarios') !!}
    {!! Form::text('destinatarios', null , ['class' => 'form-control', 'placeholder' => 'Ingrese los destinatarios', 'id' => 'destinatarios']) !!}
</div>
@if(isset($notifications_params) && $notifications_params->created_at != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($notifications_params->created_at)) }}</p>
    </div>
@endif
@if(isset($notifications_params) && $notifications_params->updated_at != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($notifications_params->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('notifications_params.index') }}" role="button">Cancelar</a>
