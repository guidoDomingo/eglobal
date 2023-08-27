<?php  
$valor = $option;

// return var_dump($valor);
?>
@extends('layout')
@section('title')
    Depósito de Arqueos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Depósito de Arqueos
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Depósitos de Arqueos</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">Búsqueda personalizada</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                        class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            {!! Form::open(['route' => 'depositos_arqueos.index', 'method' => 'GET', 'role' => 'form']) !!}
                            <div class="row"> 
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="menu_ussd_operator_id">Filtros:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="filtro"
                                            name="filtro">
                                            <option  value="">Todos</option>
                                            <option  value="Exitoso">Exitoso</option>
                                            <option  value="Pendiente">Pendiente</option>
                                        </select>
                                    </div>
                                </div>
    
                                <div class="col-md-2">
                                    <label for="search">Buscar...</label>
                                    <br />
                                    <button type="submit" class="btn btn-info"
                                        title="Buscar según los filtros en los registros." id="search" name="search">
                                        <span class="fa fa-search" aria-hidden="true"></span> &nbsp; Búsqueda
                                    </button>
                                </div>

                                <div class="col-md-2" style="margin-top: 25px" >
                                    <a style="width: 105px" href="{{ route('depositos_arqueos.create') }}" class="btn btn-primary"
                                        role="button">
                                        <span class="fa fa-plus"></span> Agregar
                                    </a>
                                </div>

                                <div class="col-md-6">
                                </div>


                            </div>
                            {!! Form::close() !!}


                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                   
                                    <th style="width:10px">#</th>
                                    <th>Fecha de la boleta</th>
                                    <th>Tipo de Pago</th>
                                    <th>Banco</th>                                
                                    <th>Numero de Boleta</th>
                                    <th>Monto</th>
                                    <th>Depositado por</th>                                                                
                                    <th>Estado</th>
                                    <th style="max-width: 150px">Respuesta</th>
                                    <th>Pocesado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deposits as $deposit)
                                <tr>
                                    <td></td>
                                    <td>{{ $deposit->fecha_boleta }}</td>
                                    <td>{{ $deposit->tipo_credito_id }}</td>
                                    <td>{{ $deposit->descripcion }}</td>
                                    <td>{{ $deposit->boleta_nro }}</td>
                                    <td>{{ number_format($deposit->amount,0,',','.') }}</td>
                                    <td>{{ $deposit->recaudador }}</td>
                                    <td>{{ $deposit->ondanet_id }}</td>
                                    <td>{{ $deposit->response_data }}</td>
                                    <td>{{ $deposit->username }}</td>
                                </tr>                                
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $deposits->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $deposits->appends(Request::only(['name']))->render() !!} 
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
        </div>
    </section>    

@endsection

@section('js')


  <!-- datatables -->
  <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
  <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

  <!-- select2 -->
  <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
  <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>



  <script type="text/javascript">
        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                //$('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        var table = $('#datatable_1').DataTable(data_table_config); 

        $( document ).ready(function() {
            document.querySelector('#filtro').value = '{{ $option['description'] }}';
        });

      

  </script>


@endsection