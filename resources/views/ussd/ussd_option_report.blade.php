@extends('layout')

@section('title')
    USSD - Opciones - Reporte
@endsection
@section('content')
    <section class="content-header">
        <h1>
            USSD - Opciones - Reporte
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">USSD - Opciones - Reporte</a></li>
        </ol>
    </section>
    <section class="content">
        @if (isset($data['list']))

            <?php $operators = $data['list']; ?>

            @for ($i = 0; $i < count($operators); $i++)

                <?php
                $id = $operators[$i]['id'];
                $description = $operators[$i]['description'];
                $updated_at = $operators[$i]['updated_at'];
                $user = $operators[$i]['user'];
                $status = $operators[$i]['status'];
                $services = $operators[$i]['services'];
                $count = $operators[$i]['count'];
                $amount_services = count($services);

                $checkted = $status == 'Activo' ? 'checked' : '';
                $title = $status == 'Activo' ? "Deshabilitar : $description" : "Habilitar $description";
                $value = $status == 'Activo' ? '1' : '0';
                ?>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $description }} ( {{ $status }} ). Tiene {{ $amount_services }}
                            servicios en total.</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Transacciones en total:</label>
                                    <p class="help-block">{{ $count }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Última actualización:</label>
                                    <p class="help-block">{{ $updated_at }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Modificado último por:</label>
                                    <p class="help-block">{{ $user }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if (\Sentinel::getUser()->hasAccess('ussd_system'))
                                    <label>Estado:</label> <br />
                                    <input type='checkbox' title='{{ $title }}' value='{{ $value }}'
                                        onclick='ussd_operator_set_status({{ $id }})' style='cursor: pointer'
                                        id='checkbox_operator_{{ $id }}' {{ $checkted }}> &nbsp; Activar /
                                    Inactivar
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <hr />
                            </div>
                        </div>

                        <div class="row">

                            @for ($j = 0; $j < count($services); $j++)

                                <?php
                                $id = $services[$j]['id'];
                                $service_id = $services[$j]['service_id'];
                                $description = $services[$j]['description'];
                                $updated_at = $services[$j]['updated_at'];
                                $status = $services[$j]['status'];
                                $user = $services[$j]['user'];
                                $options = $services[$j]['options'];
                                $amount_options = count($services[$j]['options']);

                                $checkted = $status == 'Activo' ? 'checked' : '';
                                $title = $status == 'Activo' ? "Deshabilitar : $description" : "Habilitar $description";
                                $value = $status == 'Activo' ? '1' : '0';
                                ?>

                                <div class="col-md-6">
                                    <div class="box box-default"
                                        style="border: 1px solid #d2d6de; border-width: 3px 1px 1px 1px">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">{{ $description }} ( {{ $status }} ). Tiene
                                                {{ $amount_options }} opciones en total.</h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                        class="fa fa-minus"></i></button>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Última actualización:</label>
                                                        <p class="help-block">{{ $updated_at }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    @if (\Sentinel::getUser()->hasAccess('ussd_system'))
                                                        <label>Estado:</label> <br />
                                                        <input type='checkbox' title='{{ $title }}'
                                                            value='{{ $value }}'
                                                            onclick='ussd_service_set_status({{ $id }})'
                                                            style='cursor: pointer' id='checkbox_service_{{ $id }}'
                                                            {{ $checkted }}> &nbsp; Activar /
                                                        Inactivar
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <hr />
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    @for ($k = 0; $k < count($options); $k++)
                                                        <?php
                                                        $id = $options[$k]['id'];
                                                        $description = $options[$k]['description'];
                                                        $status = $options[$k]['status'];
                                                        $amount = $options[$k]['amount'];

                                                        $checkted = $status == 'Activo' ? 'checked' : '';
                                                        $title = $status == 'Activo' ? "Deshabilitar : $description" :
                                                        "Habilitar $description";
                                                        $value = $status == 'Activo' ? '1' : '0';
                                                        ?>

                                                        <div class="callout callout-default"
                                                            style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                                            <h4>{{ $description }} ( {{ $status }} )</h4>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Última actualización:</label>
                                                                        <p class="help-block">{{ $updated_at }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    @if (\Sentinel::getUser()->hasAccess('ussd_system'))
                                                                        <label>Estado:</label> <br />
                                                                        <input type='checkbox' title='{{ $title }}'
                                                                            value='{{ $value }}'
                                                                            onclick='ussd_option_set_status({{ $id }})'
                                                                            style='cursor: pointer'
                                                                            id='checkbox_option_{{ $id }}'
                                                                            {{ $checkted }}> &nbsp; Activar /
                                                                        Inactivar
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endfor
        @endif
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
        /**
         * Editar el estado de los teléfonos.
         */
        function ussd_operator_set_status(id) {

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
         * Editar el estado del servicio
         */
        function ussd_service_set_status(id) {
            var status = null;

            if ($('#checkbox_service_' + id).is(":checked")) {
                status = true;
            } else {
                status = false;
            }

            var url = '/ussd/service/ussd_service_set_status/';

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
         * Editar el las opciones
         */
        function ussd_option_set_status(id) {

            var status = null;

            if ($('#checkbox_option_' + id).is(":checked")) {
                status = true;
            } else {
                status = false;
            }

            var url = '/ussd/option/ussd_option_set_status/';

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
    </script>
@endsection
