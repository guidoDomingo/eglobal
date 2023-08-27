<div class="form-group">
    {!! Form::label('nombre', 'Nombre') !!}
    {!! Form::text('nombre', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('ci', 'Nro. C.I.') !!}
    {!! Form::text('ci', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nro. de C.I.' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('telefono', 'Telefono') !!}
    {!! Form::text('telefono', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el teléfono' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('email', 'Email') !!}
    {!! Form::text('email', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el email' ]) !!}
</div>
@if(isset($usuario_bahia) && $usuario_bahia->created_at != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado el:') !!}
        <p> {{ date('d/m/y H:i', strtotime($usuario_bahia->created_at)) }}</p>
    </div>
@endif
@if(isset($usuario_bahia) && $usuario_bahia->updated_at != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado el:') !!}
        <p>{{ date('d/m/y H:i', strtotime($usuario_bahia->updated_at)) }}</p>
    </div>
@endif
<a class="btn btn-default" href="{{ route('usuarios_bahia.index') }}" role="button">Cancelar</a>
