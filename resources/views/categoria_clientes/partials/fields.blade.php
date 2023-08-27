
<div class="form-row">
    <div class="form-group col-md-12">
        <div class="form-group">
            {!! Form::label('descripcion', 'Nombre') !!}  <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del canal' ]) !!}
        </div>
    </div>
   
</div>  

<div class="form-row">
    <div class="form-group col-md-12">
        <div class="form-group">
          
            <div class="form-group col-md-3" style="margin-top: 25px;">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a class="btn btn-default" href="{{ route('categorias.index') }}" role="button">Cancelar</a>
                </div> 
            </div> 
        </div> 
    </div> 
</div>  
