@extends('layout')
@section('title')
Sucursales
@endsection
@section('content')
<section class="content-header">
  <h1>
    Sucursales
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Sucursales</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
  <div class="box">

    <div class="box-header">
      <h3 class="box-title">
      </h3>
        <a href="{{ route('groups.branches.create',['groupId' => $groupId]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
      <div class="box-tools">
        <div class="input-group" style="width:150px;">
          {!! Form::model(Request::only(['description']),['route' => ['groups.branches',$groupId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
          {!! Form::text('description' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
          {!! Form::close() !!}
        </div>
      </div>
    </div>
    <div class="box-body  no-padding">
     <div class="row">
      <div class="col-xs-12">
        <table class="table table-striped">
          <tbody><thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Código</th>
              <th style="width:300px">Creado</th>
              <th style="width:300px">Modificado</th>
              <th style="width:200px">Acciones</th>
            </tr>
          </thead>
          @foreach($branches as $branch)
          <tr data-id="{{ $branch->id  }}">
            <td>{{ $branch->id }}.</td>
            <td>{{ $branch->description }}</td>
            <td>{{ $branch->branch_code }}</td>
            <td>{{ date('d/m/y H:i', strtotime($branch->created_at)) }} </td>
            <td>{{ date('d/m/y H:i', strtotime($branch->updated_at)) }}
              @if($branch->updated_by != null)
                - <i>{{ $branch->updatedBy->username }}</i></td>
              @endif
            <td>
              @if (Sentinel::hasAccess('branches.delete'))
              <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar del grupo" href="#" ><i class="fa fa-remove"></i></a>
              @endif

            </td>
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
    <div class="dataTables_info" role="status" aria-live="polite">{{ $branches->total() }} registros en total</div>
  </div>
  <div class="col-sm-7">
    <div class="dataTables_paginate paging_simple_numbers">
      {!! $branches->appends(Request::only(['description']))->render() !!}
    </div>
  </div>
</div>
</div>
</div>
</div>
</section>
{!! Form::open(['route' => ['groups.branches.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
{!! Form::close() !!}

@endsection
@section('js')
<script type="text/javascript">

    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        swal({
            title: "Atención!",
            text: "Está a punto de eliminar la sucursal de este grupo, está seguro?.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#449d44",
            confirmButtonText: "Si, eliminar!",
            cancelButtonText: "No, cancelar!",
            closeOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm){
            if (isConfirm) {
                var form = $('#form-delete');
                var url = form.attr('action').replace(':ROW_ID',id);
                var data = form.serialize();
                var type = "";
                var title = "";
                $.post(url,data, function(result){
                    if(result.error == false){
                        row.fadeOut();
                        type = "success";
                        title = "Operación realizada!";
                    }else{
                        type = "error";
                        title =  "No se pudo realizar la operación"
                    }
                    swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });
            }
        });
    });
</script>
    {{-- @include('partials._delete_row_js') --}}
@endsection
@section('page_scripts')
@endsection