@extends('app')
@section('title')
Dispositivos
@endsection
@section('content')
<section class="content-header">
  <h1>
    Dispositivos
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Dispositivos</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
      <a href="{{ route('miniterminales.index') }}" class="btn-sm btn-default active" role="button">Volver</a><p>
        <div class="box-tools">
          <div class="input-group" style="width:150px;">
            {{-- {!! Form::model(Request::only(['name']),['route' => 'miniterminales.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!} --}}
            {!! Form::model(Request::only(['name']),['route' => ['devices.showGet'], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
            {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Serial Number', 'autocomplete' => 'off' ]) !!}
            {!! Form::close() !!}
        </div>
      </div>
    </div>
    <div class="box-body  no-padding">
     <div class="row">
      <div class="col-xs-12">
        <table class="table table-striped ">
          <tbody>
            <thead>
              <tr>
                <th style="width:10px">#ID</th>
                <th>Serial Number</th>
                <th>Modelo</th>              
                <th>Housing</th>
                <th>Fecha de Instalación</th>              
                <th>Activo</th>
                <th>Fecha de Activación</th>              
              </tr>
          </thead>
        
          @foreach($dispositivos as $device)
  
          <tr dispositivos-id="{{ $device->id  }}">
            <td>{{ $device->id }}.</td>
            <td>{{ $device->serialnumber }}</td>
            {{-- <td>{{ $device->descripcion }}</td> --}}
            <td>{{ $device->descripcion }}</td>
            <td>{{ $device->housing->serialnumber }}</td>
            <td>{{ $device->installation_date}} </td>
            @if ($device->activo == 1)
                <td>Sí</td>
            @else
                <td>No</td>
            @endif
            <td>{{ $device->activated_at }}  </td>
            
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="box-footer clearfix">
 <div class="row">
   <div class="col-sm-5">
    <div class="dataTables_info" role="status" aria-live="polite">{{ $dispositivos->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $dispositivos->appends(Request::only(['name']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>

@endsection
