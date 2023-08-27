<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('slug', 'Identificador') !!}
    {!! Form::text('slug', null , ['class' => 'form-control', 'placeholder' => 'Identificador único' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('description', 'Descripcion') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Descripcion' ]) !!}
</div>