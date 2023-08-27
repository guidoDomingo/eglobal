<div class="form-group">
    {!! Form::label('description', 'Nombre') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'nombre de sucursal' ]) !!}
    </div>
    <div class="form-group">
    {!! Form::label('branch_code', 'Código Sucursal (Facturación)') !!}
    
    {!! Form::text('branch_code', null , ['class' => 'form-control', 'placeholder' => 'Código de Sucursal' ]) !!}
    </div>
    <div class="form-group">
    {!! Form::label('address', 'Dirección') !!}
    {!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'dirección' ]) !!}
    </div>
    <div class="form-group">
    {!! Form::label('phone', 'Teléfono') !!}
    {!! Form::text('phone', null , ['class' => 'form-control', 'placeholder' => 'teléfono' ]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('user', 'Responsable') !!}
        {!! Form::select('user_id',$users ,$user_id , ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
    </div>
    