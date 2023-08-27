@extends('layout')
@section('title')
Configuración de Partes
@endsection
@section('content')
<section class="content-header">
  <h1>
    Configuración de Partes
    <small>Listado</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('atm.index') }}">Atms</a></li>
    <li><a href="#">Configuración de Partes</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">
        </h3>
        <div class="box-tools">
            <div class="input-group" style="width:150px;">
            {!! Form::model(Request::only(['name']),['route' => ['atm.parts', $atmId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
            {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
            {!! Form::close() !!}
            </div>
        </div>
    </div>
    {!! Form::open(['route' => ['atm.parts_update',$atmId] , 'method' => 'POST', 'role' => 'form', 'id'=>'parts-form']) !!}
    <div class="box-body  no-padding">
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped" id="partsList">
                    <tbody><thead>
                        <tr>
                        <th style="width:10px">#</th>
                        <th>Parte</th>
                        <th>Nombre</th>
                        <th style="width:220px">Denominación</th>
                        <th style="width:220px">Cant. Mínima</th>
                        <th style="width:220px">Cant. Alarma</th>
                        <th style="width:220px">Cant. Máxima</th>
                        <th style="width:50px">Activo</th>
                        </tr>
                    </thead>
                    {{-- */$index = 0/* --}}
                    @foreach($parts as $part)
                        <tr index="{{ $index }}" data-id="{{ $part->id  }}">
                            <td> {!! $part->id !!}. </td>
                            <td> {!! $part->tipo_partes !!} </td>
                            <td> {!! $part->nombre_parte !!} </td>
                            <td> {!! Form::select('denominacion['.$index.']', [50 => '50', 100 => '100', 500 => '500', 1000 => '1000', 2000 => '2.000', 5000 => '5.000', 10000 => '10.000', 20000 => '20.000', 50000 => '50.000', 100000 => '100.000'], $part->denominacion, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'denominacion']) !!}</td>
                            <td> {!! Form::text('cantidad_minima['.$index.']', $part->cantidad_minima, ['class' => 'form-control autoNumeric', 'placeholder' => '']) !!} </td>
                            <td> {!! Form::text('cantidad_alarma['.$index.']', $part->cantidad_alarma, ['class' => 'form-control autoNumeric', 'placeholder' => '']) !!} </td>
                            <td> {!! Form::text('cantidad_maxima['.$index.']', $part->cantidad_maxima, ['class' => 'form-control autoNumeric', 'placeholder' => '']) !!} 
                            {!! Form::hidden('id['.$index.']', $part->id, []) !!}
                            </td>
                            <td> {!! Form::checkbox('activo['.$index.']', $part->activo, $part->activo, ['class' => 'formCheck', 'placeholder' => '']) !!} </td>
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
                <div class="dataTables_info" role="status" aria-live="polite">{{ $parts->total() }}
                    registros en total
                </div>
            </div>
            <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    {!! $parts->appends(Request::only(['name']))->render() !!}
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
    <script src="/bower_components/admin-lte/plugins/autoNumeric/autoNumeric.js"></script>
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script>
    $(function(){
        var opciones = {aSep: '.', aDec: ',', mDec: '0', vMin: '0'};

        $('.select2').select2();
        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_flat-green',
        });

        $('#parts-form').validate({
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form){
                $('.autoNumeric').each(function(){
                    $(this).val($(this).autoNumeric('get'));
                });

                form.submit();
            }
        });

        $('.autoNumeric').autoNumeric('init', opciones);

        $('input[name^="denominacion"').each(function(index){
           $('input[name="denominacion['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese la denominación",
                }
            }); 
        });

        $('input[name^="cantidad_minima"').each(function(index){
           $('input[name="cantidad_minima['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese la cant. mínima",
                }
            }); 
        });

        $('input[name^="cantidad_alarma"').each(function(index){
           $('input[name="cantidad_alarma['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese la cant. alarma",
                }
            }); 
        });

        $('input[name^="cantidad_maxima"').each(function(index){
           $('input[name="cantidad_maxima['+index+']"]').rules('add',{
                required: true,
                messages: {
                    required: "Ingrese la canti. máxima",
                }
            }); 
        });

        $(document).on('ifChecked','.formCheck',function(){
            $(this).val(this.checked);
            console.log($(this).val());
        });
    });
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/iCheck/all.css" rel="stylesheet" type="text/css" />
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection
