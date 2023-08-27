<div class="form-row">
    <div class="form-group col-md-6 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; RETIRO DE DISPOSITIVO &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-row">
            
                <div class="form-group col-md-6">
                    {!! Form::label('numero', 'Número interno:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('numero', $numero , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero interno.' , 'readonly' => 'readonly']) !!}
                     </div>
                </div>
            
                <div class="form-group col-md-6">
                    {!! Form::label('fecha', 'Fecha:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'fecha' ]) !!}
                        {{-- {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask' =>'dd/mm/yyyy', 'alias'=> 'dd/mm/yyyy', 'data-mask' => 'dd/mm/yyyy','placeholder'=> 'dd/mm/yyyy' ]) !!} --}}
                    </div>
                </div>
                            
                <div class="form-group col-md-12">
                    {!! Form::label('encargado', 'Encargado del retiro:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('encargado', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el encargado.' ]) !!}
                     </div>
                </div>
                
                <div class="form-group col-md-12">
                    {!! Form::label('firma', 'Quien firmo:') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-pencil-square-o"></i>
                        </div>
                        {!! Form::text('firma', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el firma.' ]) !!}
                     </div>
                </div>


                <div class="form-group col-md-12">
                    {!! Form::label('retiro', 'Dispositivo retirado:') !!}
                    <br>
                        {!! Form::radio('retiro', 'si',true) !!}
                        {!! Form::label('retiro', 'Sí') !!}
                        &nbsp;  &nbsp; &nbsp;&nbsp;
                        {!! Form::radio('retiro', 'no') !!}
                        {!! Form::label('retiro', 'No') !!}
                        <br>
                        <small style="color:red"><b>Nota:</b> Al marcar "Sí", esto suspenderá la generación de las cuotas de alquiler.</small>
                        <br>
                        <small style="color:red"><b>Nota:</b> Al marcar "No", la generación de las cuotas de alquiler seguirán activas.</small>
                </div>


                <div class="form-group col-md-6">
                    {!! Form::label('imagen', 'Adjuntar comprobante de retiro:') !!}
                    <input type="file" class="filepond"  name="imagen" data-max-file-size="3MB" data-max-files="3">

                </div>
                <div class="form-group col-md-6">
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
           

             
            </div>
                {!! Form::hidden('group_id', $group_id) !!}
                {{-- {!! Form::hidden('atm_id', $atm_id) !!} --}}
            </div>
    </div>
    @include('atm_baja.info')

</div>      

<div class="clearfix"></div>
@include('partials._date_picker')






