@extends('layout')

@section('title')
Sucursal {{ $branch->name }}
@endsection
@section('content')
<section class="content-header">
  <h1>
    {{ $branch->description }}
    <small>Modificación de datos de Sucursal</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('owner.branches.index',$branch->owner_id) }}">Redes</a></li>
    <li><a href="#">{{ $branch->name }}</a></li>
    <li class="active">modificar</li>
  </ol>
</section>
<section class="content">
<div class="row">
	<div class="col-md-12">
		<div class="box box-primary">
		<div class="box-header with-border">
	      <h3 class="box-title">Modificar {{ $branch->name }}</h3>
	    </div>
	    <div class="box-body">
	    	@include('partials._flashes')
			@include('partials._messages')
			{!! Form::model($branch, ['route' => ['owner.branches.update',$branch->owner_id,$branch->id ] , 'method' => 'PUT']) !!}
			@include('branches.partials.fields')
			<button type="submit" class="btn btn-primary">Guardar</button>
			{!! Form::close() !!}
	    </div>
		</div>
		<div class="box-footer">
		
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

@include('partials._delete_form_js')
@endsection
