<div class="form-group">
    {!! Form::label('application_id', 'Aplicación') !!}
    {!! Form::select('application_id',$applications ,null , ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('service_provider_product_id', 'Pertenece a alguna marca?') !!}
    {!! Form::select('service_provider_id',$service_providers ,null , ['class' => 'form-control','placeholder' => 'Ninguna...','value'=>'0']) !!}
</div>
<div class="form-group">
    {!! Form::label('description', 'Description') !!}
    {!! Form::textarea('description', null , ['class' => 'form-control' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('refresh_time', 'Tiempo de Refresco (Segundos)') !!}
    {!! Form::text('refresh_time', null , ['class' => 'form-control' ]) !!}
</div>
<a class="btn btn-default" href="{{ route('applications.screens.index', ['application' => $app_id ] ) }}" role="button">Cancelar</a>
