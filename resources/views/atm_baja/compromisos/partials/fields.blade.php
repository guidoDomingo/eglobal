<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; COMPROMISO DE PAGO &nbsp;</h4></div>
        <div class="container-campaing">
            <div class="form-row">

                <div class="form-group col-md-12">
                    {!! Form::label('estado', 'Estado:') !!}
                    <br>
                        {!! Form::radio('estado', 'incumplido',true) !!}
                        {!! Form::label('estado', 'Incumplido') !!}
                        &nbsp;  &nbsp; &nbsp;&nbsp;
                        {!! Form::radio('estado', 'cumplido') !!}
                        {!! Form::label('estado', 'Cumplido') !!}

                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Número de compromiso:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero',  $numero , ['class' => 'form-control',  'readonly'=>'readonly'  ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('cantidad_pago', 'Cantidad de pagos:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-keyboard-o"></i>
                        </div>
                        {!! Form::text('cantidad_pago', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la cantidad de pagos' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('monto', 'Monto:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </div>
                        {!! Form::text('monto', null , ['id' => 'monto','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
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
                    <div class="form-group">
                        {!! Form::label('comentario', 'Comentario:') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comments"></i>
                            </div>
                            {{-- {!! Form::textarea('comentario', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un comentario' ]) !!} --}}
                            <textarea rows="8" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario" value=""></textarea>
                        </div>
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






