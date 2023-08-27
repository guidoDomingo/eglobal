<div class="form-group">
    {!! Form::label('description', 'Nombre') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre del flujo' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('app', 'Applicación') !!}
    {!! Form::select('app_id',$applications , null , ['class' => 'form-control chosen-select','placeholder' => 'Seleccione una aplicación']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('chkfactura', '1', false) !!}
    {!! Form::label('description', 'Expide factura?') !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Texto de ayuda') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Elemento para mostrar ayuda en pantalla' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Estado') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Estado' ]) !!}
</div>