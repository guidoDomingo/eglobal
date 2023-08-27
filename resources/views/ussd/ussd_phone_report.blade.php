@extends('layout')

@section('title')
    USSD - Teléfonos - Reporte
@endsection
@section('content')
    <style>
        .points {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

    </style>


    <section class="content-header">
        <h1>
            USSD - Teléfonos - Reporte
            <small>Teléfono/s</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">USSD - Teléfonos - Reporte</a></li>
        </ol>
    </section>
    <section class="content">



        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Teléfono/s</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if (isset($data['list']))
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <!--<tr>
                                                <th colspan="2">ATM</th>
                                                <th colspan="3">Saldo</th>
                                                <th colspan="3">GSM-Gateway</th>
                                                <th colspan="3">GSM-Gateway</th>
                                            </tr>-->
                                    <tr role="row">
                                        <th>Operadora</th>

                                        <th>#</th>
                                        <th>Teléfono</th>

                                        <th>Consulta-Saldo</th>
                                        <th>Saldo-Actual</th>
                                        <th>Saldo-Mínimo</th>

                                        <th>Señal</th>
                                        <th>Puerto</th>
                                        <th>GSM Gateway</th>

                                        <th>Transacción-Actual</th>

                                        <th>Transacciones</th>

                                        <th>Última actualización</th>
                                        <th>Estado</th>
                                        <th>Operadora</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $list = $data['list']; ?>

                                    @for ($i = 0; $i < count($list); $i++)
                                        <?php
                                        $item = $list[$i];
                                        
                                        $operator = $item['operator'];
                                        $id = $item['id'];
                                        $phone_number = $item['phone_number'];
                                        $current_amount = $item['current_amount'];
                                        $current_amount_view = $item['current_amount_view'];
                                        $signal = $item['signal'];
                                        $port = $item['port'];
                                        $reg = $item['reg'];
                                        $occupied = $item['occupied'];
                                        $minimum_amount = $item['minimum_amount'];
                                        $minimum_amount_view = $item['minimum_amount_view'];
                                        $updated_at = $item['updated_at'];
                                        $messages = $item['messages'];
                                        $final_message = $item['final_message'];
                                        
                                        $status = $item['status'];
                                        $checkted = $status == 'Activo' ? 'checked' : '';
                                        $title = $status == 'Activo' ? "Deshabilitar el número: $phone_number" : "Habilitar el número: $phone_number";
                                        $value = $status == 'Activo' ? '1' : '0';
                                        
                                        $transaction_id = $item['transaction_id'];
                                        $transaction_status = $item['transaction_status'];
                                        $transaction_count_of_day = $item['transaction_count_of_day'];
                                        
                                        $transaction_label = 'default';
                                        
                                        if ($transaction_status == 'success') {
                                            $transaction_label = 'success';
                                        } elseif ($transaction_status == 'pendiente' or $transaction_status == 'procesando') {
                                            $transaction_label = 'info';
                                        } elseif ($transaction_status == 'iniciated' or $transaction_status == 'nulled' or $transaction_status == 'reprocesando' or $transaction_status == 'canceled' or $transaction_status == 'cancelled') {
                                            $transaction_label = 'warning';
                                        } elseif ($transaction_status == 'error' or $transaction_status == 'rollback' or $transaction_status == 'error dispositivo' or $transaction_status == 'devolucion' or $transaction_status == 'inconsistency') {
                                            $transaction_label = 'danger';
                                        }
                                        
                                        ?>


                                        <tr>
                                            <td>{{ $operator }}</td>
                                            <td>#{{ $id }}</td>
                                            <td>{{ $phone_number }}</td>
                                            <td title="{{ $final_message }}" class="points">{{ $final_message }}
                                            </td>

                                            @if ($current_amount > 0)
                                                @if ($current_amount > $minimum_amount)
                                                    <td class="success" title="El saldo es óptimo.">
                                                        {{ $current_amount_view }}</td>
                                                @elseif($current_amount == $minimum_amount)
                                                    <td class="warning" title="El saldo es igual al mínimo.">
                                                        {{ $current_amount_view }}</td>
                                                @elseif($current_amount < $minimum_amount)
                                                    <td class="danger" title="El saldo es menor al mínimo.">
                                                        {{ $current_amount_view }}</td>
                                                @endif
                                            @else
                                                <td class="danger" title="El saldo es cero.">
                                                    {{ $current_amount_view }}</td>
                                            @endif

                                            <td>{{ $minimum_amount_view }}</td>

                                            @if ($signal > 0)
                                                @if ($signal >= 18)
                                                    <td class="success" title="La señal es óptima.">
                                                        {{ $signal }}</td>
                                                @elseif($signal < 18)
                                                    <td class="danger" title="La señal es débil.">
                                                        {{ $signal }}</td>
                                                @endif
                                            @else
                                                <td class="danger" title="La señal es débil.">{{ $signal }}
                                                </td>
                                            @endif

                                            <td>{{ $port }}</td>
                                            <td>{{ $reg }} - {{ $occupied }}</td>

                                            @if ($transaction_id !== null)
                                                <td
                                                    title="La transacción número: {{ $transaction_id }} está con estado: {{ $transaction_status }}">
                                                    <b>N°: {{ $transaction_id }} </b> <br />
                                                    Estado: <span class="label label-{{ $transaction_label }}">
                                                        {{ $transaction_status }}
                                                    </span>
                                                </td>
                                            @else
                                                <td>
                                                    Sin transacción actual
                                                </td>
                                            @endif

                                            <td title="Cantidad de transacciones de hoy: {{ $transaction_count_of_day }}">
                                                {{ $transaction_count_of_day }}</td>

                                            <td title="{{ $updated_at }}">{{ $updated_at }}</td>

                                            <td>{{ $status }}</td>

                                            <td>{{ $operator }}</td>
                                            <td>
                                                @if (\Sentinel::getUser()->hasAccess('ussd_system'))
                                                    <input type='checkbox' title='{{ $title }}'
                                                        value='{{ $value }}'
                                                        onclick='ussd_phone_set_status({{ $id }})'
                                                        style='float:right; cursor: pointer'
                                                        id='checkbox_phone_{{ $id }}' {{ $checkted }}>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-danger" role="alert">
                                No hay registros de <b> teléfonos</b>.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

    <!-- select2 -->
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        function reload_page() {
            swal({
                    title: 'Atención',
                    text: 'La página se recargará en 3 segundos.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                },
                function(isConfirmMessage) {

                });

            setTimeout("location.reload(true);", 3000);

        }


        /**
         * Editar el estado de los teléfonos.
         */
        function ussd_operator_set_status(id) {
            //console.log('id:', id);
            //console.log('checkbox val:', $('#checkbox_' + id).val());

            var status = null;

            if ($('#checkbox_operator_' + id).is(":checked")) {
                status = true;
            } else {
                status = false;
            }

            var url = '/ussd/operator/ussd_operator_set_status/';

            var json = {
                _token: token,
                id: id,
                status: status
            };

            $.post(url, json, function(data, status) {
                console.log('Status:', status);
                console.log('Data:', data);

                var error = data.error;
                var message = data.message;
                var type = '';
                var text = '';

                if (status == 'success') {
                    type = 'success';
                } else {
                    type = 'error';
                    text = 'Ocurrió un problema al modificar registro.';
                }

                swal({
                        title: message,
                        text: text,
                        type: type,
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            location.reload();
                        }
                    }
                );
            }).error(function(error) {
                console.log('Error al hacer post:', error);
            });
        }

        /**
         * Editar el estado de los teléfonos.
         */
        function ussd_phone_set_status(id) {
            //console.log('id:', id);
            //console.log('checkbox val:', $('#checkbox_' + id).val());

            var status = null;

            if ($('#checkbox_phone_' + id).is(":checked")) {
                status = true;
            } else {
                status = false;
            }

            var url = '/ussd/phone/ussd_phone_set_status/';

            var json = {
                _token: token,
                id: id,
                status: status
            };

            $.post(url, json, function(data, status) {
                console.log('Status:', status);
                console.log('Data:', data);

                var error = data.error;
                var message = data.message;
                var type = '';
                var text = '';

                if (status == 'success') {
                    type = 'success';
                } else {
                    type = 'error';
                    text = 'Ocurrió un problema al procesar la transacción';
                }

                swal({
                        title: message,
                        text: text,
                        type: type,
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            location.reload();
                        }
                    }
                );
            }).error(function(error) {
                console.log('Error al hacer post:', error);
            });
        }

        function ussd_operator_report() {
            var result = null;
            var url = '/ussd/operator/ussd_operator_report/';
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'html',
                async: false,
                success: function(data) {
                    result = data;
                }
            });
            return result;
        }

        var column_count = $("#datatable_1").find("tr:first th").length;
        var groupColumn = column_count - 2; //Estado y Opciones restan 2

        console.log('column_count:', column_count);
        console.log('groupColumn:', groupColumn);



        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,

            //Agrupador
            columnDefs: [{
                visible: false,
                targets: groupColumn,
                orderable: false,
            }],
            order: [],
            displayLength: 25,
            drawCallback: function(settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;

                api.column(groupColumn, {
                    page: 'current'
                }).data().each(function(group, i) {

                    console.log('group:', group);

                    if (last !== group) {

                        var color = 'dimgray';

                        /*if (group == 'Tigo') {
                            color = '#00377d';
                        } else if (group == 'Personal') {
                            color = '#00B0EB';
                        } else if (group == 'Claro') {
                            color = '#DA291C';
                        } else if (group == 'Vox') {
                            color = '#d41817';
                        }*/

                        var icon = $('<i>');
                        icon.attr('class', 'fa fa-pencil');

                        var div = $('<div>');
                        div.css({
                            'width': '100%',
                        });

                        var input = $('<input>');

                        $.get('/ussd/operator/ussd_operator_get_by_description/' + group, function(data) {

                            var item = data.data;
                            var id = item.id;
                            var status = item.status;

                            var checkted = (status == 'Activo') ? true : false;
                            var title = (status == 'Activo') ? 'Deshabilitar ' + group :
                                'Habilitar ' + group;
                            var value = (status == 'Activo') ? '1' : '0';

                            //console.log('Operador:', group);
                            //console.log('Datos:', item);
                            //console.log('checkted:', checkted);

                            input.attr({
                                'type': 'checkbox',
                                'title': title,
                                'value': value,
                                'onclick': 'ussd_operator_set_status(' + id + ')',
                                'id': 'checkbox_operator_' + id,
                                'checked': checkted
                            });

                            input.css({
                                'float': 'right',
                                'cursor': 'pointer'
                            });

                            div.append(group + ' (' + status + ')');

                            @if (\Sentinel::getUser()->hasAccess('ussd_system'))
                                div.append(input);
                            @endif
                        });

                        var td = $('<td>');
                        td.attr({
                            'colspan': (groupColumn + 1).toString(),
                            'style': 'color: white !important; border: 1px solid #fff'
                        }).append(div);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-image: linear-gradient(to right, ' +
                                color + ', #ffffff) !important; '
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = group;
                    }
                });
            },
            processing: true,
            initComplete: function(settings, json) {
                $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        var table = $('#datatable_1');
        table.DataTable(data_table_config);

        var valores = '';

        $('#datatable_1 tbody tr').each(function() {
            /*console.log($(this).html());

            var html = $.parseHTML($(this).html());
            var nodeNames = [];

            $.each(html, function(i, el) {
                console.log(el.nodeName);
            });*/
        });


        setTimeout("reload_page()", 120000);
    </script>
@endsection
