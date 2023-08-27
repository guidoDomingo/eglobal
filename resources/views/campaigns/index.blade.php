@extends('layout')
@section('title')
    Campañas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Campañas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Campañas</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                @if (Sentinel::hasAccess('campaigns.add|edit'))
                    <a href="{{ route('campaigns.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                @endif
               
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($campaigns)
                        <table id="detalles" class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="text-align:center;">Nombre</th>
                                    <th style="text-align:center;">Duración</th>
                                    <th style="text-align:center;">Flujo</th>
                                    {{-- <th style="text-align:center;">Opc. de perpetuidad</th> --}}
                                    <th style="text-align:center;">Activar campaña</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($campaigns as $campaign)
                                    <tr data-id="{{ $campaign->id  }}">
                                        <td style="text-align:center; vertical-align: middle;">{{ $campaign->id }}.</td>
                                        <td style="vertical-align: middle;">{{ $campaign->name }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ date('d/m/Y H:i:s', strtotime($campaign->start_date)) }} - {{ date('d/m/Y H:i:s', strtotime($campaign->end_date)) }}</td>
                                        {{-- <td style="text-align:center; vertical-align: middle;">{{ $campaign->duration }} días</td> --}}
                                        @if ( $campaign->flow == 1)
                                            <td style="text-align:center; vertical-align: middle;">Inicio de la transacción</td>
                                        @elseif ($campaign->flow == 2)                                     
                                            <td style="text-align:center; vertical-align: middle;">Durante la transacción</td>
                                        @elseif ($campaign->flow == 3)
                                            <td style="text-align:center; vertical-align: middle;">Al finalizar la transacción</td>
                                        @endif

                                        {{-- @if ($campaign->perpetuity == 1)
                                            <td style="text-align:center; vertical-align: middle;">Sí</td>
                                        @else
                                            <td style="text-align:center; vertical-align: middle;">No</td>
                                        @endif --}}

                                        {{-- @if ( $campaign->tipoCampaña == 1)
                                            <td style="text-align:center; vertical-align: middle;">Campaña informativa</td>
                                        @elseif ($campaign->tipoCampaña == 2)                                     
                                            <td style="text-align:center; vertical-align: middle;">Promoción de productos</td>
                                        @elseif ($campaign->tipoCampaña == 3)
                                            <td style="text-align:center; vertical-align: middle;">Promoción + venta de productos</td>
                                        @endif --}}
                                        {{-- @if (Sentinel::hasAccess('marca.add|edit')) --}}
                                            <td style="text-align:center; vertical-align: middle;">
                                                @if ($campaign->status == true)
                                                    <label class="switch">
                                                        <input type="checkbox" class="status_campaign" checked>
                                                        <span class="slider round"></span>
                                                    </label>
                                                @else
                                                    <label class="switch">
                                                        <input type="checkbox" class="status_campaign">
                                                        <span class="slider round"></span>
                                                    </label>
                                                @endif
                                            </td>
                                        {{-- @endif --}}
                                        <td style="text-align:center; width: 170px; vertical-align: middle;">
                                            @if (Sentinel::hasAccess('arts.add|edit'))
                                                <a class="btn-sm btn-primary btn-flat btn-row"  title="Arte" href="{{ route('arts.index',['campaign_id' => $campaign->id])}}"><i class="fa fa-paint-brush"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('forms.add|edit'))
                                                <a class="btn-sm btn-info btn-flat btn-row" title="Formularios" href="{{ route('forms.index',['campaign_id' => $campaign->id])}}"><i class="fa fa-list-alt"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('tickets.add|edit'))
                                                <a class="btn-sm btn-warning btn-flat btn-row" title="Tickets" href="{{ route('tickets.index',['campaign_id' => $campaign->id])}}"><i class="fa fa-ticket"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('promotions_vouchers.add|edit'))
                                                <a class="btn-sm btn-flat btn-row" style="background-color: #9f32f8; color: white;" title="Vouchers" href="{{ route('promotions_vouchers.show',['promotions_voucher' => $campaign->id])}}"><i class="fa fa-newspaper-o"></i></a>
                                            @endif
                                            <hr style=" width: 100%;">
                                            @if (Sentinel::hasAccess('asociar.add|edit'))
                                                <a class="btn-sm btn-flat btn-row" style="background-color: #130bb3; color: white;" title="Asociar ATM" href="{{ route('atmhascampagins.index',['campaign_id' => $campaign->id])}}"><i class="fa fa-check-square-o"></i></a>
                                            @endif

                                            @if (Sentinel::hasAccess('campaigns.add|edit'))
                                                <a class="btn-sm btn-success btn-flat btn-row" title="Editar ATM" href="{{ route('campaigns.edit',['campaign' => $campaign->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('campaigns.delete'))
                                                <a class="btn-delete btn-sm btn-danger btn-flat btn-row" title="Eliminar ATM" href="#" ><i class="fa fa-remove"></i> </a>
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
                    {{-- <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $campaigns->total() }} registros en total
                        </div>
                    </div> --}}
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $campaigns->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['campaigns.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}



@endsection
@section('js')
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script>
   $(document).ready(function () {
        $('#detalles').DataTable({
            "columnDefs": [{
            "targets": 0
            }],
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ resultados",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningun dato disponible en esta tabla",
                "sInfo": "Mostrando resultados _START_-_END_ de  _TOTAL_",
                "sInfoEmpty": "Mostrando resultados del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar ",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Ultimo",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
            "iDisplayLength": 50, 
            "processing": true,
            "serverSide": true,
            }
        });
    });
</script>
    <script type="text/javascript">
        $('.status_campaign').on('change', function(e) {
            e.preventDefault();
            let row = $(this).parents('tr');
            let campaign_id = row.data('id');
            let value = null;
            let mensaje = '';
            var checkeado = $(this).is(':checked');
            var thisCampaign = $(this);

            if ($(this).is(':checked')) {
                // Hacer algo si el checkbox ha sido seleccionado
                value = true;
                mensaje = 'Campaña habilitada'
            } else {
                // Hacer algo si el checkbox ha sido deseleccionado
                value = false;
                mensaje = 'Campaña desactivada'
            }

            $.post("campaigns/status_campaigns", {
                _token: token,
                _campaign_id: campaign_id,
                _value: value
            }, function(data) {
                if (data.error) {
                    thisCampaign.prop('checked', !checkeado);
                    alert('Ha ocurrido un error');
                }
                console.log('Solicitud procesada ' + mensaje);
            });
        });

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
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 30px;
            height: 17px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 13px;
            width: 13px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(13px);
            -ms-transform: translateX(13px);
            transform: translateX(13px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

    </style>
@endsection
