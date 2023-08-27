@extends('layout')

@section('title')
    Nuevo deposito de boleta
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Depósito de Boleta v2
            <small>Registrar boleta de depósito</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Depósito de Boleta</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Completa los siguientes campos:</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'depositos_boletas.store', 'method' => 'POST', 'role' => 'form', 'id' => 'form_']) !!}
                        @include('atm_baja.cobranzas.partials.fields')
                    </div>
                    <div class="box-footer">
                        <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                            <div class="btn-group mr-2" role="group" aria-label="First group">
                                <button type="submit" class="btn btn-primary pull-right submit">
                                    <i class="fa fa-save"></i> &nbsp; Guardar
                                </button>
                            </div>
                            <div class="btn-group mr-2" role="group" aria-label="Second group">
                                <a class="btn btn-default pull-right" href="{{ route('depositos_boletas.index') }}"
                                    role="button">
                                    <i class="fa fa-times"></i> &nbsp; Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h4>
                    Ante cualquier consulta o inconveniente con respecto al formulario, favor informar en el correo
                    <b> tesoreria@antell.com.py </b>
                    <br>o al correo de Atencion al Cliente
                    <b> atc@eglobalt.com.py </b>
                </h4>
            </div>
        </div>
    </section>
@endsection

@section('css')

@endsection


@section('js')
    <!-- select2 -->
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8">
    </script>

    <script type="text/javascript">
        function clean_combos() {

            $("#cuenta_bancaria_id_aux").empty();
            $("#cuenta_bancaria_id").empty();

            var option = $('<option>', {
                value: "",
                text: "Seleccione una opción"
            });

            $("#cuenta_bancaria_id_aux").append(option).trigger('change');
            $("#cuenta_bancaria_id").append(option);
        }
        ////////////////////////
        function get_atm_x_group(group_id) {

            //clean_combos();

            $("#atm_id").empty();

            var option = $('<option>', {
                value: "",
                text: "Seleccione una opción"
            });

            $("#atm_id").append(option).trigger('change');
            console.log('grupo: ',group_id);

            $.get("/get_atms_group/" + group_id , function(data) {
                console.log('respuesta: ',data);

                var atms = data.atms;

                for (var i = 0; i < atms.length; i++) {
                    var id = atms[i].id;
                    var descripcion = atms[i].name;

                    var option = $('<option>', {
                        value: id,
                        text: descripcion
                    });

                    $("#atm_id").append(option);

                    // if ($('#tipo_pago_id').val() == "1") {
                    //     if (i == 0) {
                    //         $("#banco_id").val(id).trigger('change');
                    //     }
                    // }
                }

                // if ($('#tipo_pago_id').val() == "1") {
                //     //$('#banco_id').prop('disabled', true);
                //     $('#label1').text("Número de Transferencia");
                //     $('#label2').text("Fecha de la Transferencia");
                // } else if ($('#tipo_pago_id').val() == "2") {
                //     //$('#banco_id').prop('disabled', false);
                //     $('#label1').text("Numero de Boleta del deposito");
                //     $('#label2').text("Fecha de la Boleta del deposito");
                // }
            });
            
        }

        /////////////////////////
        function get_payment_type(payment_type_id) {

            clean_combos();

            $("#banco_id").empty();

            var option = $('<option>', {
                value: "",
                text: "Seleccione una opción"
            });

            $("#banco_id").append(option).trigger('change');

            if (payment_type_id == "1" || payment_type_id == "2") {
                var atm_id = $('#atm_id').val();
                console.log(atm_id);

                $.get("/payment_type/" + payment_type_id + "/" + atm_id, function(data) {
                    var bank = data.bank;
                    var bank_account = data.bank_account;

                    for (var i = 0; i < bank.length; i++) {
                        var id = bank[i].id;
                        var descripcion = bank[i].descripcion;

                        var option = $('<option>', {
                            value: id,
                            text: descripcion
                        });

                        $("#banco_id").append(option);

                        if ($('#tipo_pago_id').val() == "1") {
                            if (i == 0) {
                                $("#banco_id").val(id).trigger('change');
                            }
                        }
                    }

                    if ($('#tipo_pago_id').val() == "1") {
                        //$('#banco_id').prop('disabled', true);
                        $('#label1').text("Número de Transferencia");
                        $('#label2').text("Fecha de la Transferencia");
                    } else if ($('#tipo_pago_id').val() == "2") {
                        //$('#banco_id').prop('disabled', false);
                        $('#label1').text("Numero de Boleta del deposito");
                        $('#label2').text("Fecha de la Boleta del deposito");
                    }
                });
            }
        }

        function get_bank_accounts(bank_id) {
            if (bank_id !== "") {
                $.get("/bank_accounts/" + bank_id, function(data) {

                    clean_combos();

                    for (var i = 0; i < data.length; i++) {
                        var id = data[i].id;
                        var numero_banco = data[i].numero_banco;

                        var option = $('<option>', {
                            value: id,
                            text: numero_banco
                        });

                        $("#cuenta_bancaria_id_aux").append(option);
                        
                        if (i == 0) { 
                            $('#cuenta_bancaria_id_aux').val(id).trigger('change');
                        }

                        $("#cuenta_bancaria_id").append(option);
                        $('#cuenta_bancaria_id').val(id);
                    }
                });
            }
        }

        $(document).ready(function() {

            $('.select2').select2({
                width: '99%'
            });

            var min_date = new Date();
            min_date.setDate(min_date.getDate() - 10);
            var max_date = new Date(new Date().setDate(new Date().getDate()));

            //Date range picker
            $('#last_update').datepicker({
                language: 'es',
                format: 'yyyy-mm-dd',
                startDate: min_date,
                endDate: max_date,
                todayHighlight: true
            }).on('changeDate', function() {
                $(this).datepicker('hide');
            });

            $('#last_update').attr({
                'onkeydown': 'return false'
            });

            //////////////////
            
            $('#group_id').on('change', function(e) {
                var group_id = e.target.value;
                get_atm_x_group(group_id);
            });
            /////////////////


            $('#tipo_pago_id').on('change', function(e) {
                var payment_type_id = e.target.value;
                get_payment_type(payment_type_id);
            });

            $('#banco_id').on('change', function(e) {
                var bank_id = e.target.value;
                get_bank_accounts(bank_id);
            });

            $('#cuenta_bancaria_id_aux').on('change', function(e) {
                var cuenta_bancaria_id_aux = e.target.value;
                console.log("cuenta_bancaria_id_aux:", cuenta_bancaria_id_aux);
            });

            $("#boleta_numero").keydown(function(event) {
                if (event.which == 69) {
                    return false;
                }
            });

            $("#boleta_numero").keyup(function(event) {
                console.log($('#boleta_numero').val());

                var amount = $('#boleta_numero').val();
                amount = (amount * 1);

                $('#boleta_numero').val(amount);
            });

            $("#monto").keydown(function(event) {
                if (event.which == 69) {
                    return false;
                }
            });

            $("#monto").keyup(function(event) {
                console.log($('#monto').val());

                var amount = $('#monto').val();
                amount = (amount * 1);

                $('#monto').val(amount);
            });

            //Para esconder las alertas
            $(".callout").delay(10000).slideUp(300);
            $('#cuenta_bancaria_id_aux').attr('disabled', 'disabled');

            get_payment_type($('#tipo_pago_id').val());

        });

    </script>
@endsection