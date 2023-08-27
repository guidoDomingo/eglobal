<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; PAGARÉ &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">

                <div class="form-group col-md-6">
                    {!! Form::label('tipo', 'Tipo de pagaré:') !!}
                    <br>
                        {!! Form::radio('tipo', 'unico', true) !!}
                        {!! Form::label('tipo', 'Único') !!}

                        &nbsp;  &nbsp; &nbsp;&nbsp;
                        {!! Form::radio('tipo', 'financiado') !!}
                        {!! Form::label('tipo', 'Financiado') !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Número interno:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control', 'readonly'=>'readonly' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('firmante', 'Titular firmante:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </div>
                        {!! Form::text('firmante', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del titular firmante.' ]) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-12">
                    {!! Form::label('monto', 'Monto:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('monto', null , ['id' => 'monto_pagare','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('cantidad_pagos', 'Cantidad de pagos:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('cantidad_pagos', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la cantidad de pagos.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('tasa_interes', 'Tasa de interes %:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-info-circle"></i>
                        </div>
                        {!! Form::text('tasa_interes', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la tasa de interes %' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('vencimiento', 'Vencimiento:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {{-- {!! Form::text('vencimiento', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!} --}}
                        {!! Form::text('vencimiento', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'vencimiento' ]) !!}
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






