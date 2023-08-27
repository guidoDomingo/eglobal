<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; REMISIÓN DE PAGARÉ &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">
                
                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Numeración interna:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control',  'readonly'=>'readonly' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('fecha', 'Fecha:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('titular_deudor', 'Titular deudor:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('titular_deudor', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del titular.' ]) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-12">
                    {!! Form::label('importe', 'Importe:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('importe', null , ['id' => 'importe','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('importe_deuda', 'Importe deuda:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('importe_deuda', null , ['id' => 'importe_deuda','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('importe_imputado', 'Importe del pagaré imputado:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('importe_imputado', null , ['id' => 'importe_imputado','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('nro_contrato', 'Número del contrato') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('nro_contrato', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el número del contrato.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('recepcionado', 'Recepcionado por:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-info-circle"></i>
                        </div>
                        {!! Form::text('recepcionado', null , ['class' => 'form-control', 'placeholder' => 'Ingrese quien recepcionó el pagaré.' ]) !!}
                     </div>
                </div>

                

                {!! Form::hidden('group_id', $group_id) !!}
            </div>
        </div>
    </div>
    @include('atm_baja.info')

</div>      

<div class="clearfix"></div>
@include('partials._date_picker')






