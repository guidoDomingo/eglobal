@extends('layout')
@section('title')
    Referencias
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Referencias
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Referencias</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('references.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'references.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Referencia', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                       
                        @if ($references)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th>Parámetros</th>
                                    <th>Reglas de servicios</th>
                                    <th>Frecuencia</th>
                                    <th>Referencia</th> 
                                    <th>Fecha de creacion</th>                                    
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($references as $reference)
                           
                                    <tr data-param-rule-id="{{ $reference->service_rule_id }}" data-service-rule-id="{{ $reference->current_params_rule_id  }}" data-reference-id="{{ $reference->reference  }}">
                                        @foreach ($paramsRules as $paramsRule )
                                            @if ($paramsRule->idparam_rules == $reference->current_params_rule_id)
                                                <td>{{ $paramsRule->description }}</td>
                                            @endif
                                        @endforeach 
                                        <td>{{ $reference->serviceRule['description'] ?? '' }}</td>
                                        <td>{{ $reference->frequency_last_updated }}</td>
                                        <td>{{ $reference->reference }}</td>
                                        <td>{{ $reference->created_at }}</td>                                                                              
                                        <td>
                                            @if (Sentinel::hasAccess('references_rules.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('references.edit',['idparam_rules' => $reference->service_rule_id ,'current_params_rule_id' => $reference->current_params_rule_id , 'reference' => $reference->reference])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('references_rules.delete'))
                                            <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $references->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $references->appends(Request::only(['description']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['references.destroy',':PARAM_RULES_ID',':CURRENT_PARAMS_RULE_ID',':REFERENCE_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')
    {{-- @include('partials._delete_row_js') --}}
    <script type="text/javascript">
        $('.btn-delete').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var service_rule_id = row.data('param-rule-id');
            var current_params_rule_id = row.data('service-rule-id');
            var reference = row.data('reference-id');
            console.log(service_rule_id);
            swal({
                title: "Atención!",
                text: "Está a punto de borrar el registro, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var form = $('#form-delete');
                    var url = form.attr('action').replace(':PARAM_RULES_ID',service_rule_id);
                    console.log(url);

                    var url = url.replace(':CURRENT_PARAMS_RULE_ID',current_params_rule_id);
                    var url = url.replace(':REFERENCE_ID',reference);
                    var data = form.serialize();
                    var type = "";
                    var title = "";
                    console.log(url);
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
@endsection
