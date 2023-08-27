@extends('layout')
@section('title')
Configuración de Parametros
@endsection
@section('content')
<section class="content-header">
  <h1>
    Configuración de Parametros
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('atm.index') }}">Atms</a></li>
    <li><a href="#">Configuración de Parametros</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">
        </h3>
            <a href="#" class="btn-sm btn-primary active" role="button" id="agregar">Agregar</a>
        <div class="box-tools">
            <div class="input-group" style="width:150px;">
            {!! Form::model(Request::only(['name']),['route' => ['atm.params', $atmId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
            {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
            {!! Form::close() !!}
            </div>
        </div>
    </div>
    {!! Form::open(['route' => ['atm.param_store',$atmId] , 'method' => 'POST', 'role' => 'form', 'id'=>'params-form']) !!}
    <div class="box-body  no-padding">
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped" id="paramsList">
                    <tbody><thead>
                        <tr>
                        <th>Parametro</th>
                        <th style="width:420px">Valor</th>
                        </tr>
                    </thead>
                    {{-- */$index = 0/* --}}
                    @foreach($params as $param)
                        <tr index="{{ $index }}" data-id="{{ $param->key  }}">
                        <td>{!! Form::text('key['.$index.']', $param->key , ['class' => 'form-control', 'placeholder' => '', 'readonly' => true]) !!}</td>
                        <td> {!! Form::text('value['.$index.']', $param->value , ['class' => 'form-control', 'placeholder' => '']) !!} </td>
                        </tr>
                        {{-- */$index++/* --}}
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="row">
            <div class="col-sm-5">
                <div class="dataTables_info" role="status" aria-live="polite">{{ $params->total() }}
                    registros en total
                </div>
            </div>
            <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    {!! $params->appends(Request::only(['name']))->render() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <a class="btn btn-default" href="{{ route('atm.index')}}" role="button">Cancelar</a>
        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
    </div>
    {!! Form::close() !!}
</div>
</section>

@endsection
@section('page_scripts')
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>

<script>
    $(function(){
        $('#params-form').validate({
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            }
        });

        $('input[name^="value"').each(function(index){
           $('input[name="value['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese el valor",
                }
            }); 
        });

        $(document).on('click','#agregar',function(e){
            e.preventDefault();
            if($('#paramsList tbody tr').length == 0){
                var index = 0
            }else{
                var index = parseInt($('#paramsList tbody tr:last').attr('index'))+parseInt(1);
            }
            var inputKey = '<input class="form-control" placeholder="" name="key['+index+']" type="text">';
            var inputValue = '<input class="form-control" placeholder="" name="value['+index+']" type="text">';
            var nuevaFila = '<tr index="'+index+'"><td>'+inputKey+'</td><td>'+inputValue+'</td></tr>'

            $('#paramsList tbody:last').append(nuevaFila);

            var urlCheck = "{{ route('atm.check_key', [$atmId]) }}";
            $('input[name="key['+index+']"]').rules('add',{
                required: true,
                remote: {
                    url: urlCheck,
                    type: "get",
                    data: {
                        key: function(){
                            return $('input[name="key['+index+']"]').val();
                        }
                    }
                },
                messages: {
                    required: "Ingrese el parametro",
                    remote: "El parametro ingresado ya existe"
                }
            });

            $('input[name="value['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese el valor",
                }
            })
        });
    });
</script>
@endsection
