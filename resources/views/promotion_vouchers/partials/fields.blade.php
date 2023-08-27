

<div class="form-row">
    <div class="form-group col-md-4">
        {!! Form::label('name', 'Nombre') !!}
        {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del voucher' ]) !!}
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('description', 'Descripción') !!}
        {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción del voucher' ]) !!}
    </div>
    {{-- <div class="form-group col-md-6">
        {!! Form::label('image', 'Imagen') !!}
        {!! Form::text('image', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la imagen del voucher' ]) !!}
    </div> --}}
    <div class="form-group col-md-4">
        {!! Form::label('cantidad', 'Cantidad') !!}
        {!! Form::number('cantidad', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la cantidad de voucher a generar' ]) !!}
    </div>
</div>
<input type="hidden" name="campaigns_id" value="{{ $campaignId }}">

<div class="clearfix"></div>
