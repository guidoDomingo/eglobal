<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; IMPUTACIÓN DE DEUDA &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">
                
                <div class="form-group col-md-12">
                    {!! Form::label('estado', 'Estado:') !!}
                    <br>
                        {!! Form::radio('estado', 'pendiente',true) !!}
                        {!! Form::label('estado', 'Pendiente') !!}
                        &nbsp;  &nbsp; &nbsp;&nbsp;
                        {!! Form::radio('estado', 'cobrado') !!}
                        {!! Form::label('estado', 'Cobrado') !!}
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Numeración interna:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('numero', $numero  , ['class' => 'form-control','readonly'=>'readonly' ]) !!}
                     </div>
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('numero_contrato', 'Número de contrato') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('numero_contrato', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero del contrato.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('fecha_siniestro', 'Fecha de siniestro:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_siniestro', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('fecha_cobro', 'Fecha de cobro:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_cobro', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('monto', 'Monto:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('monto', null , ['id' => 'monto','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('procentaje_franquicia', 'Porcentaje de franquicia:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('procentaje_franquicia', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el porcentaje.' ]) !!}
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






