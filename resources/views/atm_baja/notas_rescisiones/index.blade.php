@extends('layout')

@section('title')
    BAJA | Documentaciones
@endsection
@section('content')
    <section class="content-header">
        <h1>
            NOTA DE RESCISIÓN
            <small>Elaboracion de documentaciones</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li class="active">Nota de rescisión</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"> GRUPO: {{ $grupo->description }}</h3>
                          {{-- <a class="btn-sm btn-default pull-right" href="{{ url('atm/new/baja') }}" role="button">Atrás</a> --}}
                          <a class="btn-sm btn-default pull-right" href="{{ url('atm/new/'.$grupo->id.'/groups_atms') }}" role="button">Atrás</a>
                        @if (Sentinel::hasAccess('atms.group.rescision.add'))
                            {{-- <a href="{{ route('notarescision.create',['atm_id' => $atm->id, 'group_id' => $grupo[0]->id]) }}" class="btn-sm btn-primary pull-right active" role="button">Agregar</a> --}}
                            <a href="{{ route('notarescision.create',['atm_list' => $atm_ids, 'group_id' => $grupo->id]) }}" class="btn-sm btn-primary pull-right active" role="button">Agregar</a>
                        @endif
                    </div>
                    <div class="box-body">
                        {{-- @include('partials._flashes') --}}
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_rescision" style="font-size: 13px">
                                    <thead>
                                        <tr>
                                            <th style="width:10px; text-align:center;">#</th>
                                            <th style="text-align:center;">Número</th>
                                            <th style="text-align:center;">Nombre Comercial</th>
                                            <th style="text-align:center;">Fecha</th>
                                            <th style="text-align:center;">Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rescisiones as $item)
                                            <tr data-id="{{ $item->id }}">
                                                <td style="text-align:center; vertical-align:middle;">{{ $item->id }}.</td>
                                                <td style="text-align:center; vertical-align:middle;">{{ $item->numero }}</td>
                                                <td style="text-align:center; vertical-align:middle;">{{ $item->nombre_comercial }}</td>
                                                <td style="vertical-align:middle; text-align:center;">{{ date('d/m/Y', strtotime($item->fecha)) }}</td>
                                                <td style="vertical-align:middle; text-align:center;">
                                                    @if (Sentinel::hasAccess('atms.group.rescision.edit'))
                                                        <a class="btn btn-success btn-flat btn-row btn-sm" title="Editar" href="{{ route('notarescision.edit',['id' => $item->id])}}"><i class="fa fa-pencil"></i></a>
                                                    @endif
                                                    @if (Sentinel::hasAccess('atms.group.rescision.delete'))
                                                        <a class="btn-delete btn btn-danger btn-flat btn-row btn-sm" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['notarescision.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection
@include('partials._selectize')

@section('js')
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">            
    var data_table_config = {
            //custom
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
            displayLength: 10,
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
               // $('body > div.wrapper > header > nav > a').trigger('click');
            }
    }

    $('#datatable_rescision').DataTable(data_table_config);
</script>
<script type="text/javascript">
    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');


        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        })

        swalWithBootstrapButtons.fire({
            title: 'Atención!',
            text: 'Está a punto de borrar el registro, está seguro?.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
            }).then((result) => {
            if (result.isConfirmed) {

                var form = $('#form-delete');
                var url = form.attr('action').replace(':ROW_ID',id);
                var data = form.serialize();
                var type = "";
                var title = "";
                $.post(url,data, function(result){
                    if(result.error == false){
                        row.fadeOut();
                        swalWithBootstrapButtons.fire(
                        'Eliminado!',
                        'Operación realizada!',
                        'success'
                        )
                    }else{
                        swalWithBootstrapButtons.fire(
                        'Error!',
                        'No se pudo realizar la operación',
                        'error'
                        )
                    }
                }).fail(function (){
                    swal('No se pudo realizar la petición.');
                });

               
                
            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                'Cancelado!',
                'Operación cancelada.',
                'error'
                )
            }
            })
    });
</script>
@if (session('guardar') == 'ok')
<script>
    Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'El registro ha sido agregado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('actualizar') == 'ok')
<script>
    Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'El registro ha sido actualizado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif

@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
       /* START - CONF SPINNER */
       table.dataTable thead {background-color:rgb(179, 179, 184)}
        .lds-roller {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }
        .lds-roller div {
            animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            transform-origin: 40px 40px;
        }
        .lds-roller div:after {
            content: " ";
            display: block;
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgb(64, 83, 255);
            margin: -4px 0 0 -4px;
        }
        .lds-roller div:nth-child(1) {
            animation-delay: -0.036s;
        }
        .lds-roller div:nth-child(1):after {
            top: 63px;
            left: 63px;
        }
        .lds-roller div:nth-child(2) {
            animation-delay: -0.072s;
        }
        .lds-roller div:nth-child(2):after {
            top: 68px;
            left: 56px;
        }
        .lds-roller div:nth-child(3) {
            animation-delay: -0.108s;
        }
        .lds-roller div:nth-child(3):after {
            top: 71px;
            left: 48px;
        }
        .lds-roller div:nth-child(4) {
            animation-delay: -0.144s;
        }
        .lds-roller div:nth-child(4):after {
            top: 72px;
            left: 40px;
        }
        .lds-roller div:nth-child(5) {
            animation-delay: -0.18s;
        }
        .lds-roller div:nth-child(5):after {
            top: 71px;
            left: 32px;
        }
        .lds-roller div:nth-child(6) {
            animation-delay: -0.216s;
        }
        .lds-roller div:nth-child(6):after {
            top: 68px;
            left: 24px;
        }
        .lds-roller div:nth-child(7) {
            animation-delay: -0.252s;
        }
        .lds-roller div:nth-child(7):after {
            top: 63px;
            left: 17px;
        }
        .lds-roller div:nth-child(8) {
            animation-delay: -0.288s;
        }
        .lds-roller div:nth-child(8):after {
            top: 56px;
            left: 12px;
        }
        @keyframes lds-roller {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        /* END - CONF SPINNER */

    </style>
@endsection