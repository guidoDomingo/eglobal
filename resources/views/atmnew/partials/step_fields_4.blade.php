{{-- <div class="form-group">
    {!! Form::label('app', 'Aplicación') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-cubes"></i>
            </div>
            @if(isset($aplicaciones))
                {!! Form::select('application_id', $aplicaciones, $app_id, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
            @else
                {!! Form::select('application_id', [], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
            @endif
        </div>
    {!! Form::hidden('owner_id') !!}
    {!! Form::hidden('atm_parts',$atm_parts) !!}
    {!! Form::hidden('atm_id',null, ['id' => 'atmId']) !!}
</div> --}}
{{-- @if($atm_parts <= 0)
    {!! Form::hidden('reasignar',false, ['id' => 'reasignar']) !!}
    <div class="form-group">
        {!! Form::label('tipo_dispositivo', 'Tipo Dispositivo') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-desktop"></i>
            </div>
            {!! Form::select('tipo_dispositivo', [ '1 | 3' => 'Reciclador - 3 cassettes', '2 | 4' => 'Gran Pagador - 4 cassettes','2 | 6' => 'Gran Pagador - 6 cassettes', '3 | 0' => 'Miniterminal - Solo Box'], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione un dispositivo', 'style' => 'width:100%', 'id' => 'tipoDispositivo']) !!}
        </div>
    </div>
@else
    {!! Form::hidden('reasignar',true, ['id' => 'reasignar']) !!}
@endif --}}


{{-- 
<div class="form-group">
	{!! Form::label('user_id', 'Responsable') !!}<a style="margin-left: 8em" href='#' id="nuevoTipoUsuario" data-toggle="modal" data-target="#modalNuevoUsuario"><small>Agregar <i class="fa fa-plus"></i></small></a>
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-user"></i>
        </div>  
        {!! Form::select('user_id',$users ,$user_id , ['id' => 'user_id','class' => 'form-control select2', 'style' => 'width:100%']) !!}

    </div> 
</div> --}}


<div class="col-md-12">
    <div class="form-group">

        <div class="form-row">
            <div class="form-group col-md-12 borderd-campaing">
                <div class="title"  style="margin-left: 130PX;"><h4>&nbsp;<b>Cliente | Detalle</b> &nbsp;</h4></div>
                <div class="form-group col-md-12"  style="margin-top: 20PX;">
        
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('ruc', 'Ruc') !!}
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                {!! Form::text('ruc', isset($grupo->ruc)? $grupo->ruc: null , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('description', 'Razón Social') !!}
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                {!! Form::text('description', isset($grupo->description)? $grupo->description: null , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('telefono', 'Telefono') !!}
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                {!! Form::text('telefono',isset($grupo->telefono)? $grupo->telefono: null , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('remote_access', 'Acceso Remoto') !!}
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                {!! Form::text('remote_access',isset($network->remote_access)? $network->remote_access: null , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('correo', 'Correo') !!}
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                {!! Form::text('correo',isset($grupo_caracteristica->correo)? $grupo_caracteristica->correo: null , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        

    </div>
</div>





<div class="row">
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('app', 'Aplicación') !!}
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-cubes"></i></div>
                        @if(isset($aplicaciones))
                            {!! Form::select('application_id', $aplicaciones, $app_id, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
                        @else
                            {!! Form::select('application_id', [], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
                        @endif
                    </div>
                {!! Form::hidden('owner_id') !!}
                {!! Form::hidden('atm_parts',$atm_parts) !!}
                {!! Form::hidden('atm_id',null, ['id' => 'atmId']) !!}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                @if($atm_parts <= 0)
                    {!! Form::hidden('reasignar',false, ['id' => 'reasignar']) !!}
                    <div class="form-group">
                        {!! Form::label('tipo_dispositivo', 'Tipo Dispositivo') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-desktop"></i>
                            </div>
                            {!! Form::select('tipo_dispositivo', [ '1 | 3' => 'Reciclador - 3 cassettes', '2 | 4' => 'Gran Pagador - 4 cassettes','2 | 6' => 'Gran Pagador - 6 cassettes', '3 | 0' => 'Miniterminal - Solo Box'], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione un dispositivo', 'style' => 'width:100%', 'id' => 'tipoDispositivo']) !!}
                        </div>
                    </div>
                @else
                    {!! Form::hidden('reasignar',true, ['id' => 'reasignar']) !!}
                @endif
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('user_id', 'Responsable') !!}<a style="margin-left: 8em" href='#' id="nuevoTipoUsuario" data-toggle="modal" data-target="#modalNuevoUsuario"><small>Agregar <i class="fa fa-plus"></i></small></a>
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-user"></i></div>  
                    {!! Form::select('user_id',$users ,$user_id , ['id' => 'user_id','class' => 'form-control select2']) !!}
                </div> 
            </div>
        </div>
    </div>  
    
    <div class="col-md-6">
        @if ($posbox_status == 'No')
            <div class="col-md-12">
                <div class="form-group">
                    <div class="form-group col-md-10 borderd-campaing" style="margin-left: 20PX;" >
                        <div class="title" style="margin-left: 140PX;""><h4>&nbsp; <b> POS BOX </b>&nbsp;</h4></div>
                
                        <div class="form-group col-md-6" style="margin-top: 20PX;">
                            @if (\Sentinel::getUser()->hasAccess('pos_box_edit'))
                                <div class="form-group">
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_box', 'Si', False) !!}
                                        {!! Form::label('pos_box', 'Activo') !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="form-group col-md-6" style="margin-top: 20PX;">
                            {!! Form::label('pos_turn_1', 'Horario de atención') !!}
                            @if (\Sentinel::getUser()->hasAccess('pos_box_edit'))
                                <div class="form-group" id="horarios_atencion">
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_1', 'Si', false) !!}
                                        {!! Form::label('pos_turn_1', '09:00 Hs - 12:00 Hs') !!}
                                    </div>
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_2', 'Si', false) !!}
                                        {!! Form::label('pos_turn_2', '13:00 Hs - 18:00 Hs') !!}
                                    </div>
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_3', 'Si', false) !!}
                                        {!! Form::label('pos_turn_3', '18:00 Hs - 23:59 Hs') !!}
                                    </div>
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_4', 'Si', false) !!}
                                        {!! Form::label('pos_turn_4', '24 Hs') !!}
                                    </div>        
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::hidden('pos_box_edit', false) !!}
        @else
            <div class="col-md-12">
                <div class="form-group">
                    <div class="form-group col-md-10 borderd-campaing" style="margin-left: 20PX;" >
                        <div class="title" style="margin-left: 140PX;""><h4>&nbsp;<b>POS BOX</b> &nbsp;</h4></div>

                        <div class="form-group col-md-6" style="margin-top: 20PX;">
                            @if (\Sentinel::getUser()->hasAccess('pos_box_edit'))
                                @if ($posbox_status == 'Si')
                                    <div class="form-group">
                                        <div class="form-check">
                                            {!! Form::checkbox('pos_box', 'Si', true) !!}
                                            {!! Form::label('pos_box', 'Activo') !!}
                                        </div>
                                    </div>                                    
                                @endif
                            @endif
                        </div>

                        <div class="form-group col-md-6" style="margin-top: 20PX;">
                            {!! Form::label('pos_turn_1', 'Horario de atención') !!}
                            <div class="form-group" id="horarios_atencion">
                                
                                @if ($turno_1 == 'Si')
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_1', 'Si', true) !!}
                                        {!! Form::label('pos_turn_1', '09:00 Hs - 12:00 Hs') !!}
                                    </div>
                                @else
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_1', 'Si', false) !!}
                                        {!! Form::label('pos_turn_1', '09:00 Hs - 12:00 Hs') !!}
                                    </div>
                                @endif

                                @if ($turno_2 == 'Si')
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_2', 'Si', true) !!}
                                        {!! Form::label('pos_turn_2', '13:00 Hs - 18:00 Hs') !!}
                                    </div>
                                @else
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_2', 'Si', false) !!}
                                        {!! Form::label('pos_turn_2', '13:00 Hs - 18:00 Hs') !!}
                                    </div>
                                @endif


                                @if ($turno_3 == 'Si')
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_3', 'Si', true) !!}
                                        {!! Form::label('pos_turn_3', '18:00 Hs - 23:59 Hs') !!}
                                    </div>
                                @else
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_3', 'Si', false) !!}
                                        {!! Form::label('pos_turn_3', '18:00 Hs - 23:59 Hs') !!}
                                    </div>
                                @endif

                                @if ($turno_4 == 'Si')
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_4', 'Si', true) !!}
                                        {!! Form::label('pos_turn_4', '24 Hs') !!}
                                    </div>
                                @else
                                    <div class="form-check">
                                        {!! Form::checkbox('pos_turn_4', 'Si', false) !!}
                                        {!! Form::label('pos_turn_4', '24 Hs') !!}
                                    </div>
                                @endif
                                                
                            </div>
                        </div>

                    </div> 
                </div>
            </div>
            {!! Form::hidden('pos_box_edit', true) !!}
        @endif
    </div>
</div>