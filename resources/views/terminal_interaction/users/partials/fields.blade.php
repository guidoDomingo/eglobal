<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('description', 'Nombre') !!}
        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'Nombre de la persona', 'autocomplete' => 'off']) !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('username', 'Usuario') !!}
        {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Nombre de usuario', 'autocomplete' => 'off']) !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label for="password">Contraseña</label> <label id="label_password"></label>
        <input type="password" class="form-control" placeholder='Clave del usuario' name="password" id="password">
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <label for="password_2">Repetir Contraseña</label> <label id="label_password_2"></label>
        <input type="password" class="form-control" placeholder='Repetir clave del usuario' name="password_2" id="password_2">
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('doc_number', 'Documento') !!}
        {!! Form::text('doc_number', null, ['class' => 'form-control', 'placeholder' => 'Número de documento', 'autocomplete' => 'off']) !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('email', 'Correo') !!}
        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Correo electrónico', 'autocomplete' => 'off']) !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('branch_id', 'Sucursal:') !!}
        {!! Form::text('branch_id', null, ['class' => 'form-control input-lg', 'id' => 'branch_id', 'placeholder' => 'Seleccionar Sucursal']) !!}
    </div>
</div>

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('role_id', 'Rol de Usuario:') !!}
        {!! Form::text('role_id', null, ['class' => 'form-control input-lg', 'id' => 'role_id', 'placeholder' => 'Seleccione un Rol de Usuario']) !!}
    </div>
</div>
