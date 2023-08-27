@extends('layout')
@section('title')
    Vouchers
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Vouchers generados
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Vouchers</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                @if (Sentinel::hasAccess('promotions_vouchers.generate'))
                    <a href="{{ route('promotions_vouchers.generate.create', $campaign_id) }}" class="btn-sm btn-primary active" role="button">Generar</a>
                @endif

                @if (Sentinel::hasAccess('promotions_vouchers.import'))
                    <a href="{{ route('promotions.vouchers.generate.import',[$campaign_id]) }}" class="btn-sm btn-success active" role="button">Importar</a>
                @endif

            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($vouchers)
                        <table id="vouchers" role="grid" class="table table-bordered table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="text-align:center;">Código del voucher</th>
                                    <th style="text-align:center;">Transacción ID</th>
                                    <th style="text-align:center;">Estado del voucher</th>
                                    <th style="text-align:center; width: 200px;">Nombre</th>
                                    <th style="text-align:center;">Descripción</th>
                                    <th style="text-align:center;">Imagen asociada</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($vouchers as $voucher)
                                    <tr data-id="{{ $voucher->id  }}">
                                        <td>{{ $voucher->id }}.</td>
                                        <td style="text-align:center;"">{{ $voucher->coupon_code }}</td>
                                        {{-- <td style="text-align:center;">{{ $voucher->transaction_id }}</td> --}}
                                        @if ( $voucher->transaction_id == null)
                                            <td style="text-align:center;">--</td>
                                        @else
                                            {{-- @foreach ($transactions as $transaction )
                                                @if ($voucher->transaction_id == $transaction->id)
                                                    <td style="text-align:center;">{{$transaction->atm_transaction_id}}</td>
                                                @endif
                                            @endforeach --}}
                                            <td style="text-align:center;">{{$voucher->transaction_id}}</td>

                                        @endif
                                      
                                        @if ($voucher->status == 1)
                                            <td style="text-align:center;">Utilizado</td>
                                        @else
                                            <td style="text-align:center;">No Utilizado</td>
                                        @endif
                                        <td style="text-align:center;">{{ $voucher->name }}</td>
                                        <td style="text-align:center;">{{ $voucher->description }}</td>
                                        <td style="width: auto;">{{ $voucher->image }}.</td>                                     
                                     
                                        {{-- <td style="text-align:center; width: 100px;"> --}}
                                            {{-- @if (Sentinel::hasAccess('promotions_vouchers.add|edit')) --}}
                                            {{-- <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('promotions_vouchers.edit',['id' => $voucher->id])}}"><i class="fa fa-pencil"></i></a> --}}
                                            {{-- @endif --}}
                                            {{-- @if (Sentinel::hasAccess('promotions_vouchers.delete')) --}}
                                            {{-- <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a> --}}
                                            {{-- @endif --}}
                                        {{-- </td> --}}
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            {{-- <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <a class="btn btn-primary active" href="{{ route('campaigns.index') }}" role="button">Atrás</a>

                        <div class="dataTables_info" role="status" aria-live="polite">{{ $vouchers->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $vouchers->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </section>
    {!! Form::open(['route' => ['promotions_vouchers.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')


<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">            
    var data_table_config = {
        //custom
        fixedHeader: true,
        order: [[0, 'desc']],
        pageLength: 20,          
        lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
        ],
        dom: '<"pull-left"f><"pull-right"l>tip',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        displayLength: 10,
        processing: true,
        initComplete: function(settings, json) {
            $('#content').css('display', 'block');
            $('#div_load').css('display', 'none');
            // $('body > div.wrapper > header > nav > a').trigger('click');
        }
    }
    $('#vouchers').DataTable(data_table_config);
</script>


<script type="text/javascript">
    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
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

@if (session('actualizar') == 'ok')
<script>
    swal({
            type: 'success',
            title: 'El registro ha sido actualizado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('guardar') == 'ok')
<script>
    swal({
        type: 'success',
            title: 'El registro ha sido guardado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar registrar el contenido',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
       /* START - CONF SPINNER */
       table.dataTable thead {background-color:rgb(179, 179, 184)}
       
    </style>
@endsection