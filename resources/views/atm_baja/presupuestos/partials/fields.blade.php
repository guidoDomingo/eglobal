<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; PRESUPUESTO DE REPARACIÓN &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">

                <div class="form-group col-md-12">
                    {!! Form::label('numero', 'Número interno:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control', 'readonly' => 'readonly' ]) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-12">
                    {!! Form::label('fecha', 'Fecha:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'fecha' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-12">
                    {!! Form::label('concepto', 'Concepto:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('concepto', 'INT107' , ['class' => 'form-control', 'readonly' => true  ]) !!}
                    </div>
                    <br>
                    <small style="color:red"><b>Nota:</b>Se ejecutará el procedimiento para la generación de la factura crédito</small>
                </div>
               
                <div class="form-group col-md-12">
                    {!! Form::label('monto', 'Monto total:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('monto', null , ['class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="form-group">
                        {!! Form::label('comentario', 'Detalle de reparación:') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comments"></i>
                            </div>
                            <textarea rows="4" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario"></textarea>
                        </div>
                    </div>
                </div>

                {!! Form::hidden('group_id', $group_id) !!}
                {!! Form::hidden('idtransaccion', $idtransaccion) !!}

            </div>
        </div>
    </div>
    @include('atm_baja.info')

</div>      

<div class="clearfix"></div>
@include('partials._date_picker')






