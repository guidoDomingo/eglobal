<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; NOTA DE RETIRO &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">
                <div class="form-group col-md-12">
                    {!! Form::label('fecha', 'Fecha de retiro:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'fecha' ]) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('nombre_comercial', 'Nombre comercial:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('nombre_comercial', $grupo->description , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre comercial' , 'readonly' => 'readonly']) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('propietario', 'Propietario/a:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('propietario', Null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del propietario/a']) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('direccion', 'Dirección comercial:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-map"></i>
                        </div>
                        {!! Form::text('direccion', $grupo->direccion , ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección' ]) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('referencia', 'Referencia o motivo:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('referencia', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la referencia o el motivo de la nota.' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('ruc_representante', 'RUC o CI del representante legal:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-black-tie"></i>
                        </div>
                        {!! Form::text('ruc_representante', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el RUC o CI.' ]) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('representante_legal', 'Representante legal:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-black-tie"></i>
                        </div>
                        {!! Form::text('representante_legal', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre comercial' ]) !!}
                    </div>
                </div>

                {{-- <div class="form-group col-md-6">
                    {!! Form::label('correos', 'Destinatarios:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-at"></i>
                        </div>
                        <div class="input-group input-group-md">
                            <input type="text" id="correos" name="correos" class="form-control" value='["logisticas@eglobalt.com","baja@eglobalt.com"]'>
                            <span class="input-group-btn">
                              <button class="btn red" type="button"><i class="fa fa-calendar-plus-o"></i></button>
                            </span>
                          </div>

                    </div>
                </div> --}}
                
                {!! Form::hidden('group_id', $group_id) !!}
                {{-- {!! Form::hidden('atm_id', $atm_id) !!} --}}
            </div>
        </div>
    </div>
    @include('atm_baja.info')
</div>      

<div class="clearfix"></div>
@include('partials._date_picker')






