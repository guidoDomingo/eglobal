<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; NOTA DE RESCISIÓN &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">

                <div class="form-group col-md-12">
                    {!! Form::label('numero', 'Número interno:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero interno de la nota de rescisión','readonly'=>'readonly' ]) !!}
                    </div>
                </div>
            
                <div class="form-group col-md-12">
                    {!! Form::label('nombre_comercial', 'Nombre comercial:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('nombre_comercial', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre comercial.' ]) !!}
                    </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('direccion', 'Dirección:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('direccion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección del comercial.' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('fecha', 'Fecha:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {{-- {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!} --}}
                        {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'fecha' ]) !!}
                    </div>
                </div>
                {!! Form::hidden('group_id', $group_id) !!}
                {{-- {!! Form::hidden('atm_id', $atm_id) !!} --}}
            </div>
        </div>
    </div>
    @include('atm_baja.info')
</div>      

<div class="clearfix"></div>
@include('partials._date_picker')






