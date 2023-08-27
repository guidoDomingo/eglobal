@extends('layout')

@section('title')
    Nuevo deposito de Cuota
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Deposito de Cuota
            <small>Registro de deposito de Cuota</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Deposito de Cuota</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Deposito de Cuota</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'depositos_cuotas.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('depositos_cuotas.partials.fields')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('depositos_cuotas.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right submit" disabled="disabled" id='guardar'>Guardar</button>
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

        /*console.log($('#cuota_monto').val());
        console.log($('#cuota_numero').text());*/

        $('#monto').on('change keydown paste input',function(){
            /*var cuotas= $(this).val() / parseInt($('#cuota_monto').val());*/
            var monto=$(this).val();
            var atm_id = $('#atm_id').val();
            //console.log(atm_id);

            $.get("/get_cuotas/" + atm_id, function(data) {
                var cuota_monto = data.cuota_monto;
                var cuota_numero = data.cuota_numero;
                $('#cuota_monto').val(cuota_monto)
                //console.log(monto);
                var cuotas= monto / cuota_monto;
                //console.log(cuotas);
                if(Number.isInteger(cuotas)){
                    if(cuotas == 1 ){
                        cuotas=cuota_numero;
                        $('#cant_cuotas').text("El Monto " + parseInt(monto) + " afectara la cuota " + parseInt(cuotas));
                    }else{
                        cuotas= cuotas - 1;
                        var cuota_hasta= parseInt(cuota_numero) + cuotas;
                        $('#cant_cuotas').text("El Monto " + monto+ " afectara cuotas desde " + cuota_numero + " al " + parseInt(cuota_hasta));
                    }
                    document.getElementById("guardar").disabled = false;
                }else{
                    document.getElementById("guardar").disabled = true;
                    $('#cant_cuotas').text("El siguiente monto es un monto invalido");
                }

            });
            
            
        });

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

        function isDoubleClicked(element) {
            //if already clicked return TRUE to indicate this click is not allowed
            if (element.data("isclicked")) return true;
            //mark as clicked for 3 second
            element.data("isclicked", true);
            setTimeout(function () {
                element.removeData("isclicked");
        }, 5000);

        //return FALSE to indicate this click was allowed
        return false;
        }

        $('.submit').on("click", function () {
        if (isDoubleClicked($(this))) return;
        });

    </script>
@endsection