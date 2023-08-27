<div class="form-group">
    {!! Form::label('controls', 'Controles') !!}
    {!! Form::select('object_id',$screen_objects , null , ['class' => 'form-control chosen-select']) !!}
</div>

<div class="form-group">
    {!! Form::label('value', 'Valor') !!}
    {!! Form::text('value', null , ['class' => 'form-control', 'placeholder' => 'Asignar valor por defecto' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('model', 'Modelo al que corresponde') !!}
    {!! Form::select('service_model',$models , null , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno']) !!}
</div>

<div class="form-group">
    {!! Form::label('types', 'Tipo') !!}
    {!! Form::select('tipo',$objects_types , null , ['class' => 'form-control chosen-select','placeholder' => 'Seleccione tipo de objeto']) !!}
</div>

<h4>Validaciones</h4>
<div class="form-group">
    {!! Form::label('longitud_min', 'Longitud mínima') !!}
    {!! Form::text('longitud_min', null , ['class' => 'form-control', 'placeholder' => 'Longitud mínima de caracteres requerido' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('longitud_max', 'Longitud máxima') !!}
    {!! Form::text('longitud_max', null , ['class' => 'form-control', 'placeholder' => 'Longitud máxima de caracteres requerido' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('min_value', 'Valor mínimo') !!}
    {!! Form::text('valorMinimo', null , ['class' => 'form-control', 'placeholder' => 'Valor mínimo requerido' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('is_required', 'Es un campo requerido') !!}
    {!! Form::checkbox('requerido', 1 , true) !!}
</div>

<div class="form-group">
    {!! Form::label('is_editable', 'Es un campo editable') !!}
    {!! Form::checkbox('editable', 1 , true) !!}
</div>

<div class="form-group">
    {!! Form::label('is_hidden', 'Es un campo oculto') !!}
    {!! Form::checkbox('oculto', 1 , false) !!}
</div>

<div class="form-group">
    {!! Form::label('err_msg', 'Mensaje de error') !!}
    {!! Form::text('err_msg', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un mensaje para caso de error' ]) !!}
</div>
<h4>Acciones</h4>
<div class="form-group">
    {!! Form::label('service_request', 'Service Request') !!}
    {!! Form::select('request_service_id',$service_requests , null , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno']) !!}
</div>

<div class="form-group">
    {!! Form::label('model', 'Modelo que actualiza') !!}
    {!! Form::select('controller_model',$models , null , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno']) !!}
</div>

<div class="form-group">
    {!! Form::label('on_success', 'En caso de éxito ir a') !!}
    {!! Form::select('on_success_path',$screens , null , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno']) !!}
</div>

<div class="form-group">
    {!! Form::label('on_fail', 'En caso de fallo ir a') !!}
    {!! Form::select('on_fail_path',$screens , null , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno','value'=>0]) !!}
</div>