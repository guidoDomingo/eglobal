<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; RECIBO DE PÉRDIDA &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">
            
                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Número interno') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero',  $numero , ['class' => 'form-control','readonly'=>'readonly' ]) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-6">
                    {!! Form::label('fecha_finiquito', 'Fecha de finiquito:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha_finiquito', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                    </div>
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('forma_cobro', 'Forma de cobro:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('forma_cobro', null , ['class' => 'form-control', 'placeholder' => 'Ejemplo: TRASL' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-6">
                    {!! Form::label('valor', 'Valor:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('valor', null , ['class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                     </div>
                </div>

                {{-- <div class="form-group col-md-6">
                    {!! Form::label('imagen', 'Adjuntar comprobante de retiro:') !!}
                    <input type="file" class="filepond"  name="imagen" data-max-file-size="3MB" data-max-files="3">

                </div> --}}
                <div class="form-group col-md-12">
                    {!! Form::label('gestionado', 'Gestionado por:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('gestionado', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del encargado.' ]) !!}
                     </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="form-group">
                        {!! Form::label('comentario', 'Comentario:') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-comments"></i>
                            </div>
                            <textarea rows="10" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario" value=""></textarea>
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






