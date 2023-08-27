<div class="form-row">

    <div class="form-group col-md-3">
        <div class="form-group">
              {!! Form::label('promotions_providers_id', 'Proveedor:') !!} 
            {!! Form::select('promotions_providers_id', $providers, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('business_id', 'Negocio:') !!} 
          {!! Form::select('business_id', $business, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
      </div>
    </div>
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre:') !!}
            {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la sucursal' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('address', 'Dirección:') !!}
            {!! Form::text('address', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección' ]) !!}
        </div>
    </div>
</div>  
<div class="form-row">
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('phone', 'Teléfono:') !!}
            {!! Form::text('phone', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero de telefono' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('provider_branch_id', 'ID Proveedor:') !!}
            {!! Form::text('provider_branch_id', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el ID del proveedor' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('latitud', 'Latitud:') !!}
            {!! Form::text('latitud', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la latitud' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('longitud', 'Longitud:') !!}
            {!! Form::text('longitud', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la longitud' ]) !!}
        </div>
    </div>

    <div class="form-group col-md-3">
        <div class="bootstrap-timepicker">
            <div class="form-group">
                <label>Horario de atención - Desde:</label>
                <div class="input-group">
                    <input type="text" class="form-control timepicker" name="start_time"> 
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="bootstrap-timepicker">
            <div class="form-group">
                <label>Horario de atención - Hasta:</label>
                <div class="input-group">
                    <input type="text" class="form-control timepicker" name="end_time">
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('location', 'URL de la ubicación (maps):') !!}
            {!! Form::text('location', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la url de google maps' ]) !!}
        </div>
    </div>

    <div class="form-row">
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('custom_image', 'Archivo multimedia') !!} 
            {{-- <small>Formatos soportados : [Vídeo] <b>MP4</b> | Hasta 50 Mb  [Imágen] <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</small> --}}
            <h5>Formatos soportados</h5>
            <h5>Vídeo: <b>MP4</b> | Hasta 50 Mb</h5>
            <h5>Imágen: <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</h5>
        </div>
    </div>
    <div class="form-group col-md-3 borderd-content">
        <div class="form-group">
            <input type="file" class="filepond" name="custom_image" data-max-file-size="50MB" data-max-files="1">
            @if(isset($content))
                <small style="">Nota: cargar un archivo multimedia solo en caso de querer modificar el actual</small>
            @endif
        </div>
    </div>
</div>   
</div>  
    

<div class="form-row">
    <div class="form-group col-md-12">
        <div class="form-group">
          
            <div class="form-group col-md-3" style="margin-top: 25px;">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a class="btn btn-default" href="{{ route('branches_providers.index') }}" role="button">Cancelar</a>
                </div> 
            </div> 
        </div> 
    </div> 
</div>  
@section('page_scripts')
@append