@extends('layout')

@section('title')
Nueva Sucursal
@endsection
@section('content')
<section class="content-header">
  <h1>
    Sucursal
    <small>Creación de Sucursal</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Sucursal</a></li>
    <li class="active">agregar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Nueva Sucursal</h3>
	    </div>
	    <div class="box-body">
			@include('partials._messages')
			{!! Form::open(['route' => ['owner.branches.store', $ownerId] , 'method' => 'POST', 'role' => 'form']) !!}
			@include('branches.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>

	</div>
</div>
</section>
@endsection
@section('page_scripts')

<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();
</script>
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection

