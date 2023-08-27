@extends('app')

@section('title')
Dashboard
@endsection
@section('refresh')
<meta http-equiv="refresh" content="900">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
@endsection
@section('aditional_css')
<link type="text/css" href="/dashboard/plugins/amcharts/plugins/export/export.css" rel="stylesheet">
@endsection
@section('content')

{{-- @if (\Sentinel::getUser()->hasAccess('dash.tesoreria'))
        <section class="content-header">
            <h1>
                Saldo Epin
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            </ol>
        </section>
        <section class="content">

            <div class="col-lg-6 col-xs-12" id="principal"></div>
        </section>

    @else --}}

<section class="content-header">
    <h1>
        Dashboard
        <small>Monitoreo de la red</small>
        
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    </ol>
</section>

<!-- dashboard content -->
<section class="content">

    {{-- @if (\Sentinel::getUser()->hasAccess('monitoreo.saldo'))
                <div class="col-lg-6 col-xs-12" id="principal"></div>
            @endif --}}

    <!--<div class="box box-default">

        <div class="box-header with-border">
            <h3 class="box-title">
                Dashboard
                <small>Monitoreo de la red</small>
            </h3>
        </div>

        <div class="box-body">-->

            <div class="row">
                
                <div class="col-md-8">

                    {{-- <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.atms'))
                            <div class="box box-default">
                                <div class="box-header with-border">

                                    <h3 class="box-title">ATMS</h3>

                                    <div class="box-tools pull-right" style="cursor:pointer">
                                        <!--<i id="reload_data_pie" style="margin: 10px;" class="fa fa-refresh pull-right" title="Actualizar" data-toggle="tooltip"></i>-->

                                        <label class="radio-inline">
                                            <input type="radio" name="redes" checked="checked" value="todos">Todos
                                        </label>

                                        <label class="radio-inline">
                                            <input type="radio" name="redes" value="terminales">Terminales
                                        </label>
                                                
                                        <label class="radio-inline">
                                            <input type="radio" name="redes" value="miniterminales">Miniterminales
                                        </label>

                                        <button class="btn btn-default" type="button" title="Actualizar" style="margin-left: 10px; background: transparent; color: #333; border:none; outline: none; border-radius: 25%; padding: 2px;" id="reload_data_pie">
                                            <span class="fa fa-refresh"></span>
                                        </button>
                                    </div>

                                </div>
                                <div class="box-body">
                                    <div id="atm_spinn" class="text-center" style="margin: 50px 10px"><i class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>
                                    <div class="graficoAtm" id="graficoAtm"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div> --}}
                    <livewire:graficos>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('superuser') and \Sentinel::getUser()->hasAccess('monitoreo.transacciones'))
                            <div class="box">
                                <div class="box-header with-border" style="text-align: center">
                                    <h3 id='graph-title' class="box-title"></h3>
                                </div>
                                <div class="row">
                                    <div class="col-md-5" style="margin:15px 10px; text-align: center">
                                        <label class="radio-inline">
                                            <input type="radio" name="report" checked="checked" value="daily">Diario
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="report" value="weekly">Semanal
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="report" value="monthly">Mensual
                                        </label>
                                    </div>
                                    <div class="col-md-6" style="margin:10px;">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input readonly="readonly" name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ $reservationtime ?? '' }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="box-body">
                                    @include('dashboard.general_admin')
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                        
                    <div class="row">

                            @if (\Sentinel::getUser()->hasAccess('mantenimiento.clave'))
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header with-border" style="text-align: center;">
                                            <h3 class="box-title">Solicitud de Clave</h3>
                                            <i id="reload_keys" style="margin:0px 15px; cursor:pointer" class="fa fa-refresh"></i>
                                        </div>
                                        <div class="row" style="height: 550px; overflow: scroll">
                                            <div id="keys_spinn" class="text-center" style="margin: 50px 10px"><i class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>

                                            <div id="keys_content" class="col-md-4" style="margin:5px">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (\Sentinel::getUser()->hasAccess('minis_cashout_devolucion_vuelto'))
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Retiro de Dinero</h3>
                                            <i id="reload_retiro" style="margin:0px 15px; cursor:pointer" class="fa fa-refresh"></i>
                                        </div>
                                        <div class="row" style="height: 550px; overflow: scroll">
                                            <div id="retiro_spinn" class="text-center" style="margin: 50px 10px"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>

                                            <div id="retiro_content" class="col-md-4" style="margin:5px">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                    </div>

                </div>

                <div class="col-md-4">
                    
                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.servicios'))
                            <div class="info-box">
                                <span class="info-box-icon bg-purple"><i class="fa fa-cube" style="position: absolute; top:21px; left: 38px;"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Servicios</span>
                                    <span class="service_info info-box-number"> </span>
                                    <a href="/webservices" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.saldos'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-money" style="position: absolute; top:21px; left: 38px;"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Saldos al Límite</span>
                                        <span class="balances_info info-box-number"></span>
                                        <a href="/dashboard/balances" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.conciliaciones'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fa fa-retweet" style="position: absolute; top:21px; left: 38px;"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Conciliaciones Pendientes</span>
                                        <span class="conciliations_info info-box-number" style="font-size: 12px"></span>
                                        <a href="/reports/conciliations_details" target="_blank" class="small-box-footer">Detalles
                                            <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.alertas'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-warning" style="position: absolute; top:21px; left: 38px;"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Alertas</span>
                                        <span class="warning_info info-box-number"></span>
                                        <a href="/reports/notifications" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.billetaje'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-bus" style="position: absolute; top:21px; left: 38px;"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transacciones Billetaje</span>
                                        <span class="rollback_info info-box-number"></span>
                                        <a href="/reports/rollback" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.transacionmontocero'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-genderless" style="position: absolute; top:21px; left: 45px;"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transacciones</span>
                                        <span class="monto_cero_info info-box-number"></span>
                                        <a href="/reports/success_zero" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.ventasPendientesExtractos'))
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-refresh" style="position: absolute; top:21px; left: 38px;"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Ventas</span>
                                        <span class="pendiente_info info-box-number"></span>
                                        <a href="/reports/movements_affecting_extracts" target="_blank" class="small-box-footer">Detalles <i class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @if (\Sentinel::getUser()->hasAccess('monitoreo.saldo') || \Sentinel::getUser()->inRole('superuser'))
                                <div class="col-md-12" id="principal">

                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        <!--</div>
    </div>-->


    <div id="modal_detalle_mini" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 99%;">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Detalle de la Transacción
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_miniCashOut">
                                <thead>
                                    <tr>
                                        <th style="text-align:center; background: #d2d6de;">Referencia</th>
                                        <th id="monto" style="text-align:center; background: #d2d6de;">Monto</th>
                                        <th id="comision" style="text-align:center; background: #d2d6de;">Comisión
                                        </th>
                                        <th style="text-align:center; background: #d2d6de;">Monto a Entregar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="text-align:center;">
                                        <td id="referenciatd"></td>
                                        <td id="montotd"></td>
                                        <td id="comisiontd"></td>
                                        <td id="entregartd"></td>
                                        <td id="id"></td>
                                        <td id="atm_id"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="modal-footer" style="text-align: center">

                        <button id="procesar" class="btn btn-success  mr-auto">
                            <span class="fa fa-table"></span> &nbsp; Procesar
                        </button>

                        <button class="btn btn-danger" onclick="modal_detalle_close()">
                            <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </div>



    <div id="modal_detalle_cancel" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 99%;">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Motivo de la cancelación
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4" id="micheckbox">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="check1" value="Saldo insuficiente">
                                    <label class="form-check-label" for="inlineRadio1">Saldo insuficiente</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="check2" value="Datos incorrectos">
                                    <label class="form-check-label" for="inlineRadio2">Datos incorrectos</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="check3" value="Otro motivo">
                                    <label class="form-check-label" for="inlineRadio3">Otro motivo</label>
                                </div>
                            </div>

                            <div class="col-md-4" id="divData">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label" id="idData"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="modal-footer" style="text-align: center">

                            <button class="btn btn-success  mr-auto" id="cancel">
                                <span class="fa fa-table"></span> &nbsp; Enviar
                            </button>

                            <button class="btn btn-danger" onclick="modal_detalle_cancel_close()">
                                <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                            </button>

                        </div>
                    </div>

                </div>
            </div>
        </div>

</section>
{{-- @endif --}}

@append
<style>
    .graficoAtm {
        width: 100%;
        height: 350px;
        font-size: 11px;
    }

    #principal {
        height: 170px;
        width: 100%;
        /* margin-left: 25%; */
        border-radius: 25px;
        background-color: transparent;
    }
</style>
@section('js')

<script src="/dashboard/plugins/amcharts/amcharts.js"></script>
<script src="/dashboard/plugins/amcharts/serial.js"></script>
<script src="/dashboard/plugins/amcharts/pie.js"></script>
<script src="/dashboard/plugins/amcharts/plugins/export/export.min.js"></script>
<script src="/dashboard/plugins/amcharts/themes/dark.js"></script>
<script src="/dashboard/plugins/amcharts/lang/es.js"></script>

<script type="application/javascript">
    {{-- $('input[name=redes]').click(function() {
        dashboard.main.elements.atms_general($(this).val())
    }); --}}

    $('input[name=report]').click(function() {
        console.log($(this).val());
        dashboard.main.elements.transactions($(this).val())
    });

    $("#reservationtime").change(function() {
        dashboard.main.elements.transactions($(this).val())
    });

    $('#reload_keys').click(function() {
        dashboard.main.elements.refresh()
    });

    $('#keys_content').on("click", "li", function() {
        var key_id = $(this).data("id");
        dashboard.main.elements.showkey(key_id);
    });

    $('#reload_retiro').click(function() {
        dashboard.main.elements.refreshAtm('')
    });

    function obtenerAtm() {
        let id = $("#atms").val();
        dashboard.main.elements.refreshAtm(id);
    }

    function modalView(data) {

        let json = jQuery.parseJSON(data['parameters']);
        let parameter = jQuery.parseJSON(json);

        $("#id").text(data.id);
        $("#id").hide();
        $("#atm_id").text(data.atm_id);
        $("#atm_id").hide();
        $("#modal_detalle_mini").modal();
        $("#monto").hide();
        $("#comision").hide();
        $("#montotd").hide();
        $("#comisiontd").hide();

        let amount;

        switch (data.marca) {
            case 'Claro Billetera':
                amount = NumberFormat(parameter.monto);
                $("#referenciatd").text(parameter.numero_destino);
                $("#entregartd").text(amount);
                break;

            case 'Billetera Personal':
                amount = NumberFormat(parameter.amount);
                $("#referenciatd").text(parameter.source_msisdn);
                $("#entregartd").text(amount);
                break;

            case 'Tigo Money':
                amount = NumberFormat(parameter.amount);
                $("#referenciatd").text(parameter.msisdn);
                $("#entregartd").text(amount);
                break;

            case 'Telebingo':
                amount = NumberFormat(parameter.amount);
                $("#referenciatd").text(parameter.Rcaridout);
                $("#entregartd").text(amount);
                break;

            case 'Apostala':
                amount = NumberFormat(parameter.amount);
                let calculation = NumberFormat(parameter.calculation);
                let subtraction = NumberFormat(parameter.subtraction);
                $("#referenciatd").text(parameter.ci);
                $("#montotd").text(amount);
                $("#comisiontd").text(calculation);
                $("#entregartd").text(subtraction);

                $("#montotd").show();
                $("#comisiontd").show();
                $("#monto").show();
                $("#comision").show();
                break;

                // case 'Quiniela': 
                // $("#referenciatd").text(parameter.ticket);
                // $("#entregartd").text('A definirse');
                //     break;
            default:

                $("#referenciatd").text('No existe dato');
                $("#entregartd").text('No existe dato');
        }

        if (data.tipo == 'Devolucion' || data.tipo == 'Vuelto') {
            amount = NumberFormat(parameter.valor_entrega);
            $("#referenciatd").text(data.tipo);
            $("#entregartd").text(amount);
        }
    }

    function NumberFormat(number) {
        let amount = new Intl.NumberFormat('es-MX').format(number);
        return amount;
    }

    function modalViewCancel(id) {
        $("#divData").hide();
        $("#idData").text(id)
        $("#modal_detalle_cancel").modal();
    }

    function validadorCheck() {

        let cancel = '';

        $('#check1')

        if ($('#check1').prop('checked')) {
            cancel = $('#check1').val();
        }
        if ($('#check2').prop('checked')) {
            cancel = $('#check2').val();
        }
        if ($('#check3').prop('checked')) {
            cancel = $('#check3').val();
        }
        if (cancel == '') {

            swal({
                    title: 'Acción no válida',
                    text: 'Favor, seleccione una opción para realizar el envío',
                    type: 'error',
                    confirmButtonText: "Aceptar"
                },
                function(isConfirm) {});

        }

        return cancel;
    }

    $(document).ready(function() {

        $("#cancel").click(function(e) {
            e.preventDefault();
            var id = $("#idData").text()
            // $("#cancel").prop('disabled', true);

            cancel = validadorCheck();

            if (cancel != '') {
                console.log(cancel);

                var url = '/cancelMiniMoney';
                var type = "";
                var title = "";

                $.post(url, {
                    _token: token,
                    id: id,
                    motivo: cancel
                }, function(result) {
                    if (result.error == true) {
                        type = "error";
                        title = "No se pudo realizar la operación";
                        $("#procesar").prop('disabled', false);

                    } else {
                        type = "success";
                        title = "Operación realizada!" /*+result.amount*/ ;
                        $("#procesar").prop('disabled', false);
                    }
                    swal({
                            title: title,
                            text: result.message,
                            type: type,
                            confirmButtonText: "Aceptar"
                        },
                        function(isConfirm) {
                            location.reload();
                        });
                }).fail(function() {
                    swal('No se pudo realizar la petición.');
                });

                $("#micheckbox").modal('hide');
            }


        });

        //para relizar el procesar
        $("#procesar").click(function(e) {
            e.preventDefault();
            var id = $("#id").text()
            var atm_id = $("#atm_id").text();
            $("#procesar").prop('disabled', true);

            var url = '/successMiniMoney';
            var type = "";
            var title = "";

            $("#retiro_spinn").show();
            $("#retiro_content").hide();

            $.post(url, {
                _token: token,
                id: id,
                atm_id: atm_id
            }, function(result) {
                $("#retiro_spinn").hide();
                $("#retiro_content").show();
                console.log(result);
                if (result.error == true) {
                    type = "error";
                    title = "No se pudo realizar la operación";
                    $("#procesar").prop('disabled', false);

                } else {
                    type = "success";
                    title = "Operación realizada!" /*+result.amount*/ ;
                    $("#procesar").prop('disabled', false);
                }
                swal({
                        title: title,
                        text: result.message,
                        type: type,
                        confirmButtonText: "Aceptar"
                    },
                    function(isConfirm) {
                        location.reload();
                    });
            }).fail(function() {
                swal('No se pudo realizar la petición.');
            });

            $("#modal_detalle_mini").modal('hide');

        });
    });

    function modal_detalle_close() {
        $("#modal_detalle_mini").modal('hide');
    }

    function modal_detalle_cancel_close() {
        $("#modal_detalle_cancel").modal('hide');
    }

    function danger(data) {
        var id = $(data).data('value');

        var url = '/cancelMiniMoney';
        var type = "";
        var title = "";

        $.post(url, {
            _token: token,
            id: id
        }, function(result) {
            if (result.error == true) {
                type = "error";
                title = "No se pudo realizar la operación";

            } else {
                type = "success";
                title = "Operación realizada!";
            }
            swal({
                    title: title,
                    text: result.message,
                    type: type,
                    confirmButtonText: "Aceptar"
                },
                function(isConfirm) {
                    location.reload();
                });
        }).fail(function() {
            swal('No se pudo realizar la petición.');
        });
    }

    {{-- $('#reload_data_pie').click(function() {
        var red = $('input[name=redes]:checked').val();
        dashboard.main.elements.atms_general(red);
    }); --}}
</script>

<script src="/dashboard/graphs.js"></script>
<!--
    Comentado, el código que estaba en este archivo, ahora está en este blade para mejor manejo.
    <script src="/dashboard/dash.objects.js"></script>
-->

<!-- InputMask -->
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

<script>

    var $errorHtml = '<div title="Error al consultar" class="animated fadeIn text-center"><i class="fa fa-exclamation-triangle"></i><br></div>';
    var urlGetDetalle = '/dashboard/atms_detalles/';

    var dashboard =  {
        main:{
            elements:{
                atms: function(){
                    $.post("/dashboard/atms", {_token: token }, function( data ) {
                        if(data.status){
                            $(".atm_info").html(data.result.message);
                        }else{
                            $(".atm_info").html("");
                        }

                    }).error(function(){
                        $(".atm_info").html($errorHtml);
                    });


                },
                services: function(){
                    $.post("/dashboard/services", {_token: token }, function( data ) {

                        if(data.status){
                            $(".service_info").html(data.result.message);
                        }else{
                            $(".service_info").html("");
                        }

                    }).error(function(){
                        $(".service_info").html($errorHtml);
                    });
                },
                atm_balances: function(){
                    $.post("/dashboard/balances", {_token: token }, function( data ) {

                        if(data.status){
                            $(".balances_info").html(data.result.message);
                        }else{
                            $(".balances_info").html("");
                        }

                    }).error(function(){
                        $(".balances_info").html($errorHtml);
                    });
                },
                warnings:function(){
                    /*

                    Comentado porque explota: 

                    $.post("/dashboard/warnings", {_token: token }, function( data ) {

                        if(data.status){
                            $(".warning_info").html(data.result.message);
                        }else{
                            $(".warning_info").html("");
                        }

                    }).error(function(){
                        $(".warning_info_info").html($errorHtml);
                    });
                    */
                },
                rollback:function(){
                    $.post("/dashboard/rollback", {_token: token }, function( data ) {

                        if(data.status){
                            $(".rollback_info").html(data.result.message);
                        }else{
                            $(".rollback_info").html("");
                        }

                    }).error(function(){
                        $(".rollback_info").html($errorHtml);
                    });
                },
                montoCero:function(){
                    $.post("/dashboard/montoCero", {_token: token }, function( data ) {

                        if(data.status){
                            $(".monto_cero_info").html(data.result.message);
                        }else{
                            $(".monto_cero_info").html("");
                        }

                    }).error(function(){
                        $(".monto_cero_info").html($errorHtml);
                    });
                },
                pendiente:function(){
                    $.post("/dashboard/pendiente", {_token: token }, function( data ) {

                        if(data.status){
                            $(".pendiente_info").html(data.result.message);
                        }else{
                            $(".pendiente_info").html("");
                        }

                    }).error(function(){
                        $(".pendiente_info").html($errorHtml);
                    });
                },
                conciliations:function(){
                    $.post("/dashboard/conciliations", {_token: token }, function( data ) {

                        if(data.status){
                            $(".conciliations_info").html(data.result.message);
                        }else{
                            $(".conciliations_info").html("");
                        }

                    }).error(function(){
                        $(".conciliations_info").html($errorHtml);
                    });
                },
                transactions:function(frecuency){
                    $("#graph_spinn").show();  
                    $("#chartdiv").hide(); 
                    $.post("/dashboard/transactions", {_token: token, _frecuency: frecuency},function(data) {
                        if(data.status){
                            graphs.lines('title',data.result.data)
                            $("#graph-title").html(data.result.dates);
                            $("#graph_spinn").hide();
                            $("#chartdiv").show(); 
                        }else{
                            $("#chartdiv").html($errorHtml);
                        }

                        console.log('hizo pos');
                    }).error(function(){
                
                        $("#chartdiv").html($errorHtml);
                    });


                },
                refresh:function(){
                    $("#keys_content").hide();
                    $("#keys_spinn").show();
                    $.post("/dashboard/keys", {_token: token }, function( data ) {
                        if(data.status){
                            $("#keys_spinn").hide();
                            $("#keys_content").html(data.result.message);
                            $("#keys_content").show();
                        }else{
                            $("#keys_spinn").hide();
                            $(".keys_content").html("");
                            $("#keys_content").show();
                        }

                    }).error(function(){
                        $("#keys_spinn").hide();
                        $(".keys_content").html($errorHtml);
                        $("#keys_content").show();
                    });
                },
                showkey:function(key_id){
                    var key_pass    = '#pass_'+key_id;
                    var key_eye     = '#eye_'+key_id;
                    var key_forb     = '#forb_'+key_id;
                    $.post("/dashboard/show_keys", {_token: token,_key_id: key_id }, function( data ) {
                        if(data.status){
                            $(key_pass).html(data.result.message);
                            $(key_eye).hide();
                            if(data.result == -213){
                                $(key_forb).show();
                            }
                        }else{
                            $(key_pass).html('Error');
                            $(key_eye).hide();
                        }
                    });
                },
                refreshAtm:function(id){
                    $("#retiro_content").hide();
                    $("#retiro_spinn").show();
                    $.post("/dashboard/atmsView", {_token: token, id: id }, function( data ) {
                        if(data.status){
                            $("#retiro_spinn").hide();
                            $("#retiro_content").html(data.result.message);
                            $("#retiro_content").show();
                        }else{
                            $("#retiro_spinn").hide();
                            $(".retiro_content").html("");
                            $("#retiro_content").show();
                        }

                    }).error(function(){
                        $("#retiro_spinn").hide();
                        $(".retiro_content").html($errorHtml);
                        $("#retiro_content").show();
                    });
                },
                {{-- atms_general:function(redes){                
                    $("#graficoAtm").hide();
                    $("#atm_spinn").show();

                    $.post("/dashboard/atms_general", {_token: token, _redes: redes },function(data) {
                        var valores = data.result.data;

                        var chart = AmCharts.makeChart("graficoAtm", {
                            // "language": "es",
                            "type": "pie",
                            "startDuration": 0,
                            "pullOutDuration": 0,
                            "pullOutRadius": 0,
                            "radius": 80,
                            "theme": "none",
                            "addClassNames": true,
                            "legend":{
                                "position":"bottom",
                                "autoMargins":true
                            },
                            "colorField": "color",
                            "innerRadius": "20%",
                            "fontFamily": "Helvetica",
                            "defs": {
                                "filter": [{
                                    "id": "shadow",
                                    "width": "200%",
                                    "height": "200%",
                                    "feOffset": {
                                        "result": "offOut",
                                        "in": "SourceAlpha",
                                        "dx": 0,
                                        "dy": 0
                                    },
                                    "feGaussianBlur": {
                                        "result": "blurOut",
                                        "in": "offOut",
                                        "stdDeviation": 5
                                    },
                                    "feBlend": {
                                        "in": "SourceGraphic",
                                        "in2": "blurOut",
                                        "mode": "normal"
                                    }
                                }]
                            },
                            "dataProvider": [
                                {
                                    "estado": "Cap. Máxima",
                                    "minutos": valores.capacidad_maxima,
                                    "color": "#00008e",
                                    "param": "capacidad_maxima"
                                }, 
                                {
                                    "estado": "Cant. Mínima",
                                    "minutos": valores.cantidad_minima,
                                    "color": "#00b8ef",
                                    "param": "cantidad_minima"
                                },
                                {
                                    "estado": "Online",
                                    "minutos": valores.online,
                                    "color": "#0A8B19",
                                    "param": "online"
                                }, 
                                {
                                    "estado": "Offline",
                                    "minutos": valores.offline,
                                    "color": "#FDB504",
                                    "param": "offline"
                                }, 
                                {
                                    "estado": "Suspendido",
                                    "minutos": valores.suspendido,
                                    "color": "#FD0404",
                                    "param": "suspendido"
                                },
                                {
                                    "estado": "Bloqueados",
                                    "minutos": valores.bloqueados,
                                    "color": "#770000",
                                    "param": "bloqueados"
                                }, 
                            ],
                            "valueField": "minutos",
                            "titleField": "estado",
                            "export": {
                                "enabled": true,
                                "label": "Exportar",
                            }
                        });

                        chart.addListener("clickSlice", handleClick);

                        function handleClick(e)
                        {
                            if(e.dataItem.dataContext.param == 'capacidad_maxima'){
                                $('.actual').show();
                                $('.maxima').show();
                            }else{
                                $('.maxima').hide();
                                $('.actual').hide();
                            }

                            $("#modal-contenido").html('');
                            $("#modal-footer").html('');
                            console.log(urlGetDetalle+e.dataItem.dataContext.param+'/'+redes);
                            $.get(urlGetDetalle+e.dataItem.dataContext.param+'/'+redes, 
                            {
                                status: e.dataItem.dataContext.param,
                                redes: redes
                            },
                            function(data) {
                                $("#modal-contenido").html(data.modal_contenido);
                                $("#modal-footer").html(data.modal_footer);
                                $("#modalDetalleAtms").modal('show');
                            });
                        }
                        $("#atm_spinn").hide();
                        $("#graficoAtm").show();
                    }).error(function(){
                        $("#modal-contenido").html($errorHtml);
                    });
                }, --}}
                balance_online: function(){
                    $.post("/dashboard/balance_online", {_token: token }, function( data ) {
                        console.log(data);

                        var principal_html = '';
                        var bg_class = 'gray';
                        var epin_estado = 'Error en la consulta';
                        var credit_online = 'Sin información';
                        var moneda = 'Sin información';

                        if(data.status) {

                            credit_online = data.result.data.credit;
                            moneda = data.result.data.moneda;

                            if(data.result.data.valor > 30000000) {

                                bg_class = 'green';
                                epin_estado = 'Estado: OK';

                            } else if(data.result.data.valor <= 30000000 && data.result.data.valor > 20000000) {

                                bg_class = 'yellow';
                                epin_estado = 'Estado: Saldo bajo';

                            } else if(data.result.data.valor <= 20000000 && data.result.data.valor > 5000000) {

                                bg_class = 'orange';
                                epin_estado = 'Estado: Crítico';

                            } else if(data.result.data.valor >= 0 && data.result.data.valor <= 5000000){

                                bg_class = 'red';
                                epin_estado = 'Estado: Sin saldo';

                            }

                        }

                        principal_html += '<div class="small-box bg-' + bg_class + '" style="border-radius: 15px;">';
                        principal_html += '     <div class="inner" style="padding: 20px">';
                        principal_html += '         <h3 class="credit_online">' + credit_online + '</h3>';
                        principal_html += '         <h4 class="moneda">' + moneda + '</h4>';
                        principal_html += '     </div>';
                        principal_html += '     <div class="icon" style="margin-top: 60px; margin-right: 10px;">';
                        principal_html += '         <i class="fa fa-money"></i>';
                        principal_html += '     </div>';
                        principal_html += '     <h4 class="small-box-footer">Saldo EPIN ( ' + epin_estado + ' )</h4>';
                        principal_html += '</div>';

                        $('#principal').append(principal_html);

                    }).error(function(){
                        $(".atm_info").html($errorHtml);
                    });


                },
            },
            load:function(){

                @if (\Sentinel::getUser()->hasAccess('monitoreo.saldo') || \Sentinel::getUser()->inRole('superuser'))
                    dashboard.main.elements.balance_online();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.atms'))
                    dashboard.main.elements.atms();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.servicios'))
                    dashboard.main.elements.services();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.saldos'))
                    dashboard.main.elements.atm_balances();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.alertas'))
                    dashboard.main.elements.warnings();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.billetaje'))
                    dashboard.main.elements.rollback();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.transacionmontocero'))
                    dashboard.main.elements.montoCero();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.ventasPendientesExtractos'))
                    dashboard.main.elements.pendiente();
                @endif

                @if (\Sentinel::getUser()->hasAccess('monitoreo.conciliaciones'))
                    dashboard.main.elements.conciliations();
                @endif

                //Se agrega esta validación solo para que el super user pueda ver esta información
                @if (\Sentinel::getUser()->hasAccess('superuser') and \Sentinel::getUser()->hasAccess('monitoreo.transacciones'))
                    dashboard.main.elements.transactions('daily');
                @endif

                @if (\Sentinel::getUser()->hasAccess('mantenimiento.clave'))
                    dashboard.main.elements.refresh();
                @endif

                @if (\Sentinel::getUser()->hasAccess('minis_cashout_devolucion_vuelto'))
                    dashboard.main.elements.refreshAtm();
                @endif

                {{-- @if (\Sentinel::getUser()->hasAccess('monitoreo.atms'))
                    dashboard.main.elements.atms_general('todos');
                @endif --}}

            }
        }
    };

    dashboard.main.load();


    //-------------------------------------------------------------------------------------------------------------------------------------------------------------------









































    //Datemask dd/mm/yyyy
    $("#datemask").inputmask("dd/mm/yyyy", {
        "placeholder": "dd/mm/yyyy"
    });
    //Datemask2 mm/dd/yyyy
    $("#datemask2").inputmask("mm/dd/yyyy", {
        "placeholder": "mm/dd/yyyy"
    });
    //reservation date preset
    $('#reservationtime').val()
    if ($('#reservationtime').val() == '' || $('#reservationtime').val() == 0) {
        var date = new Date();
        var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
        var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

        $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
    }
    //Date range picker
    $('#reservation').daterangepicker();

    $('#reservationtime').daterangepicker({


        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Ultimos 7 Dias': [moment().subtract(6, 'days'), moment()],
            'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                'month')]
        },
        dateLimit: {
            'months': 1,
            'days': -1,

        },
        minDate: new Date(2000, 1 - 1, 1),
        maxDate: new Date(),
        showDropdowns: true,

        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre',
                'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1,
            format: 'DD/MM/YYYY H:mm',
        },

        format: 'DD/MM/YYYY HH:mm:ss',
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),

    });

    $(document).on('click', '.pay-info', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var atm_id = row.data('id');
        $.get('{{ url('reports') }}/info/atm_notification/' + atm_id,
            function(data) {
                $(".idAtm").html(atm_id);
                $("#modal-contenido-notifications").html(data);
                $("#detalles").show();
                $('#keys_spinn').hide();
                $('#process-reactivacion').hide();
                $('#message_box').hide();
                $("#myModal").modal();
            });
    });

    $(document).on('click', '.detalle_minimo', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var atm_id = row.data('id');
        $.get('{{ url('dashboard') }}/detalle_cantidad_minima/' + atm_id,
            function(data) {
                $(".idAtm").html(atm_id);
                $("#modal-contenido-cantidades").html(data.modal_contenido);
                $("#detalles").show();
                $('#keys_spinn').hide();
                $('#process-reactivacion').hide();
                $('#message_box').hide();
                $("#modal-cantidades-minimas").modal();
            });
    });

    $(document).on('hidden.bs.modal', '.modal', function() {
        $('.modal:visible').length && $(document.body).addClass('modal-open');
    });
</script>

@endsection
<!-- Modal detalle atms-->
<div id="modalDetalleAtms" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Listado de ATMs<label class="labelRed"></label></h4>
            </div>
            <div class="modal-body" style="overflow:scroll;width:100%;overflow:auto">
                <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info" style="font-size: 14px;">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Nombre</th>
                            <th>Identificador</th>
                            <th>Red</th>
                            <th>Estado</th>
                            <th style="width:150px">Ultima Actualización</th>
                            <th style="width:150px">Tiempo Transcurrido</th>
                            <th class="actual">Cant. Actual</th>
                            <th class="maxima">Cap. Máxima</th>
                            <th>App Versions</th>
                        </tr>
                    </thead>
                    <tbody id="modal-contenido">

                    </tbody>
                    <tfoot id="modal-footer">
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>

    </div>
</div>


<!-- Modal notificaciones-->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Detalles - ATM <label class="idAtm"></label></h4>
            </div>
            <div class="modal-body" style="overflow:scroll;width:100%;overflow:auto">
                <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info" style="font-size: 14px;">
                    <thead>
                        <tr role="row">
                            <th class="sorting_disabled" rowspan="1" colspan="1">Dispositivo</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Mensaje</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Inicio</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Fin</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Tiempo Transcurrido</th>
                        </tr>
                    </thead>
                    <tbody id="modal-contenido-notifications">

                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Dispositivo</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Mensaje</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Inicio</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Fin</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Tiempo Transcurrido</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
<!-- Modal cantidades minimas-->
<div id="modal-cantidades-minimas" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Detalles - ATM <label class="idAtm"></label></h4>
            </div>
            <div class="modal-body" style="overflow:scroll;width:100%;overflow:auto">
                <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info" style="font-size: 14px;">
                    <thead>
                        <tr role="row">
                            <th class="sorting_disabled" rowspan="1" colspan="1">Nombre Parte</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Denominación</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Cant. Mínima/ Cant. Actual
                            </th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="modal-contenido-cantidades">

                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Nombre Parte</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Denominación</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Cant. Mínima/ Cant. Actual
                            </th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Estado</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->