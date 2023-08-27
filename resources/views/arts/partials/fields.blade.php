
<div class="form-row">
    <div class="form-group col-md-12">
        {!! Form::label('title', 'Titulo') !!}
        {!! Form::text('title', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el titulo del arte' ]) !!}
    </div>
    {{-- <div class="form-group col-md-12">
        {!! Form::label('image', 'Imagen') !!}
        {!! Form::text('image', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la url de la imagen' ]) !!}
    </div> --}}
    <div class="form-group col-md-12">
        {!! Form::label('image', 'Imagen Asociada') !!}
        <input type="file" class="filepond" name="image" data-max-file-size="35MB" data-max-files="3">
        @if(isset($art))
            <small style="">Nota: cargar una imagen solo en caso de querer modificar la imagen actual</small>
        @endif
    </div>
</div>

<div class="clearfix"></div>

<div class="form-row">
    <div class="form-group col-md-6">
        {!! Form::label('duracionReprodu', 'Duración de reproducción (Seg.)') !!}
        {!! Form::text('duracionReprodu', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la duración de la reproducción' ]) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('duracionPausa', 'Duración de la pausa (Seg.)') !!}
        {!! Form::text('duracionPausa', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la duración de la pausa' ]) !!}
    </div>
</div>

<div class="clearfix"></div>
{!! Form::hidden('campaigns_id', $campaign_id) !!}

{{-- <div class="form-row">
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('campaigns_id', 'Campañas/Promociones') !!} 
            {!! Form::select('campaigns_id', $campaigns, $campaigns_id, ['id' => 'atmId','class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => 'Seleccione una campaña']) !!}
        </div>
    </div>
</div>
<div class="clearfix"></div> --}}
