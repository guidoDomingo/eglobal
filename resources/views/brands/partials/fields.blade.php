{{-- <div class="form-group">
    {!! Form::label('id', 'Id') !!}
    {!! Form::text('id', null , ['class' => 'form-control', 'placeholder' => 'Serial' ]) !!}
</div> --}}
<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::text('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Descripción de la marca']) !!}
</div>
<div class="form-group">
    <div class="form-check">
        {{-- {!! Form::checkbox('priority', 1, false) !!} 
        {!! Form::checkbox('priority', 1, $modelo->priority ? true : false) !!}
        {!! Form::label('priority', 'Prioritario') !!} --}}
    </div>
</div>

<a class="btn btn-default" href="{{ route('brands.index') }}" role="button">Cancelar</a>
