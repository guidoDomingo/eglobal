<div class="box box-success">
	<div class="box-header with-border">
      <h3 class="box-title">Agregar Petición</h3>
    </div>
    <div class="box-body">
    	<div id="form-alert-container">
    	<div id="form-alert" class="" style="display:none">
		    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		    <h4><i class="icon fa fa-check"></i></h4>
		    <p></p>
		    <ul></ul>
		</div>
		</div>
		{!! Form::open(['route' => 'webservicerequests.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'form-ws-request']) !!}
		<div class="form-group">
		{!! Form::label('endpoint', 'Terminación de URL') !!}
		{!! Form::text('endpoint', null , ['id' => 'endpoint', 'class' => 'form-control', 'placeholder' => 'clients/{parameter}/accounts' ]) !!}
		</div>
		<div class="form-group">
		{!! Form::label('keyword', 'Clave') !!}
		{!! Form::text('keyword', null , ['id' => 'keyword', 'class' => 'form-control', 'placeholder' => 'service_name_client_accounts' ]) !!}
		</div>
		<div class="form-group">
		{!! Form::label('transactional', 'Transaccional') !!}
		{!! Form::checkbox('transactional', true) !!}
		</div>
		<div class="form-group">
		  {!! Form::label('service_provider_product_id', 'Producto Relacionado') !!}
		  {!! Form::select('service_provider_product_id', $wsproducts ,null , ['disabled' => 'disabled', 'class' => 'form-control object-type','placeholder' => 'Seleccione un Producto Relacionado...']) !!}
		</div>
		<div class="form-group">
		{!! Form::label('cacheable', 'Cachear consulta') !!}
		{!! Form::checkbox('cacheable', true) !!}
		</div>

		<a class="btn btn-default" id="wsrequest-form-clear" href="#" role="button">Limpiar</a>
		<button type="submit" id="wsrequest-submit" class="btn btn-primary">Guardar</button>
		{!! Form::close() !!}
    </div>
</div>
@section('page_scripts')
@include('webservices.partials.js._js_requests')
@endsection