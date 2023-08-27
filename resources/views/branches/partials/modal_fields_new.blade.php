<div class="form-group">
{!! Form::label('description', 'Nombre') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-pencil"></i>
        </div>  
		{!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'nombre de sucursal' , 'id' => 'description_sucursal_new']) !!}
    </div>
</div>
<div class="form-group">
{!! Form::label('branch_code', 'Código Sucursal (Facturación)') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-key"></i>
        </div>  
		{!! Form::text('branch_code', '001' , ['class' => 'form-control', 'placeholder' => 'Código de Sucursal' ]) !!}
    </div>
</div>
<div class="form-group">
{!! Form::label('address', 'Dirección') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-map-marker"></i>
        </div>  
		{!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'dirección' ]) !!}
    </div>
</div>
<div class="form-group">
{!! Form::label('phone', 'Teléfono') !!}
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-phone-square"></i>
        </div>  
		{!! Form::text('phone', null , ['class' => 'form-control', 'placeholder' => 'teléfono' ]) !!}
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('more_info', 'Horario de atención') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>  
                {!! Form::text('more_info', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el horario de atención' ]) !!}
            </div> 
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('executive_id', 'Ejecutivo responsable') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-user"></i>
                </div>  
                {!! Form::select('executive_id',$users ,null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
            </div> 
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('caracteristicas_id', 'Caracteristicas del comercial') !!} <a style="margin-left: 2em" href='#' id="nuevaCaracteristica" data-toggle="modal" data-target="#modalCaracteristicas"><small>Agregar <i class="fa fa-plus"></i></small></a>
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-map-marker"></i>
                </div>  
                {!! Form::select('caracteristicas_id', $caracteristicas ,null,['class' => 'form-control select2', 'placeholder' => 'Agregue una caracteristica' , 'style' => 'width:100%']) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('related_id', 'Encargado del ATM') !!} 
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-map-marker"></i>
                </div>  
                {{-- {!! Form::select('related_id', $responsables ,null,['class' => 'form-control select2', 'style' => 'width:100%']) !!} --}}
                {!! Form::select('related_id',$responsables ,null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}

            </div>
        </div>
    </div>
</div>
{!! Form::hidden('user_id',-1, ['user_id' => 'null']) !!}


