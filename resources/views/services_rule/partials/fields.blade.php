<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Descripción' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('message_user', 'Mensaje') !!}
    {!! Form::text('message_user', null , ['class' => 'form-control', 'placeholder' => 'Mensaje' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('redes', 'Redes') !!}
    {!! Form::select('owner_id', $owners ,null , ['id' => 'owner_id','class' => 'form-control select2']) !!}
</div>

<div class="form-group">
    {!! Form::label('servicios', 'Servicio') !!}
    {!! Form::select('marca_id', $services ,null , ['id' => 'marca_id','class' => 'form-control select2']) !!}
</div>
