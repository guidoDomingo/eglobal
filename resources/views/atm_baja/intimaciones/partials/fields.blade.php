<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; INTIMACIÓN &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">

                <div class="form-group col-md-12">
                    {!! Form::label('numero', 'Número interno') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control', 'readonly'=>'readonly' ]) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-12">
                    {!! Form::label('fecha_envio', 'Fecha de envío:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_envio', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('fecha_recepcion', 'Fecha de recepción:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_recepcion', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>
             

                <div class="form-group col-md-12">
                    {!! Form::label('fecha_vencimiento', 'Fecha de vencimiento:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_vencimiento', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
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






