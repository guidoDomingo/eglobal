

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('redes', 'Redes') !!} @if (\Sentinel::getUser()->inRole('superuser')) <a style="margin-left: 8em" href='#' id="nuevaRed" data-toggle="modal" data-target="#modalNuevaRed"><small>Agregar <i class="fa fa-plus"></i></small></a> @endif
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-sitemap"></i>
                </div>  
                {!! Form::select('owner_id', $owners, null, ['id' => 'owner_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder' => 'Seleccione una Red..']) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group"  id="grilla" style="display: none">
            {!! Form::label('grilla_completa', 'Grilla completa') !!} &nbsp;
            <br>
            {!! Form::label('si', 'SÍ') !!}
            {!! Form::radio('grilla_completa', 'si', false) !!}&nbsp;&nbsp;
            {!! Form::label('no', 'No') !!}
            {!! Form::radio('grilla_completa', 'no', true) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-keyboard-o"></i>
        </div>  
        {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('code', 'Código Identificador') !!}
    @if(isset($atm->id))
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-key"></i>
        </div>  
    {!! Form::text('code', null , ['class' => 'form-control', 'placeholder' => 'Código' ]) !!}
    </div>
    @else
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-key"></i>
        </div>  
    {!! Form::text('code', $atm_code , ['class' => 'form-control', 'placeholder' => 'Código','readonly'=>true]) !!}
    </div>
    @endif
</div>
<div class="form-group">
    {!! Form::label('public_key', 'Clave Pública') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-unlock-alt"></i>
        </div>  
        {!! Form::text('public_key', isset($public_key)? $public_key : null , ['class' => 'form-control key', 'readonly'=>'readonly', 'id' => 'public_key', 'placeholder' => 'clave' ]) !!}
    </div>
    {!! Form::button('Nueva clave', ['class' => 'btn btn-warning btn-generate-key']) !!}
</div>
<div class="form-group">
    {!! Form::label('private_key', 'Clave Privada') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-lock"></i>
        </div>  
        {!! Form::text('private_key', isset($private_key) ? $private_key : null , ['class' => 'form-control key', 'readonly'=>'readonly', 'id' => 'private_key', 'placeholder' => 'clave' ]) !!}
        </div>
    {!! Form::button('Nueva Clave', ['class' => 'btn btn-warning btn-generate-key']) !!}
</div> 
@if(isset($atm->id))
    {!! Form::hidden('id',$atm->id, ['id' => 'id']) !!}
@endif
