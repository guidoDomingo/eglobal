@extends('layout')

@section('title')
    Cashout
@endsection

@section('content')

    <section class="content-header">
        <h1>
            Cashout
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Cashout</a></li>
            <li class="active">Mantenimiento</li>
        </ol>
    </section>

    <section class="content">

        <div class="delay_slide_up">
            @include('partials._flashes')
        </div>

        <div class="row">

            <div class="col-md-6">

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">Información</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        @if (isset($data['headboard']))

                                            <?php
                                            $branch = $data['headboard']['branch'];
                                            $point_of_sale = $data['headboard']['point_of_sale'];
                                            $terminal = $data['headboard']['terminal'];
                                            $user = $data['headboard']['user'];
                                            $cashouts = $data['headboard']['cashouts'];
                                            $amount_available = $data['headboard']['amount_available'];
                                            $amount_available_view = $data['headboard']['amount_available_view'];
                                            $percentage = $data['headboard']['percentage'];
                                            $color = 'red';
                                            
                                            if ($amount_available > 0) {
                                                $color = 'green';
                                            }
                                            
                                            ?>

                                            <div class="info-box bg-{{ $color }}">
                                                <span class="info-box-icon">
                                                    <i class="fa fa-money"></i>
                                                </span>

                                                <div class="info-box-content">
                                                    <span class="info-box-text">Disponible:</span>
                                                    <span class="info-box-number">{{ $amount_available_view }}</span>

                                                    <div class="progress">
                                                        <div class="progress-bar"></div>
                                                    </div>
                                                    <span class="progress-description">
                                                        Este monto representa la opción seleccionada
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="callout callout-default"
                                                style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                                <h4>Sucursal: </h4> {{ $branch }}
                                            </div>

                                            
                                            <div class="callout callout-default"
                                                style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                                <h4>Usuario encargado: </h4> {{ $user }}
                                            </div>

                                            <!--<div class="callout callout-default"
                                                style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                                <h4>Punto de venta: </h4> {{ $point_of_sale }}
                                            </div>

                                            <div class="callout callout-default"
                                                style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                                <h4>Terminal: </h4> {{ $terminal }}
                                            </div>-->
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">Reglas de Cashout</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                            class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    @if (isset($data['list']))

                                        <?php $list = $data['list']; ?>

                                        @for ($i = 0; $i < count($list); $i++)

                                            <?php
                                            $parameters = $list[$i];
                                            $option = $parameters['option'];
                                            $option_checked = $parameters['option_checked'];
                                            $description = $parameters['description'];
                                            $amount = $parameters['amount'];
                                            $amount_disabled = $parameters['amount_disabled'];
                                            $amount_type = $parameters['amount_type'];
                                            
                                            $parameters = json_encode($parameters);
                                            
                                            $color = '#d2d6de';
                                            
                                            if ($option_checked == 'checked') {
                                                $color = '#00c0ef';
                                            }
                                            
                                            ?>

                                            <div class="col-md-12">
                                                <div class="callout callout-default"
                                                    style="border: 1px solid {{ $color }}; border-width: 1px 1px 1px 4px"
                                                    title="{{ $description }}">

                                                    <h4>{{ $option }}) {{ $description }}:</h4>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label>Estado:</label> <br />
                                                            <input type='checkbox' value=''
                                                                onclick='save({{ $parameters }}, 1)' style='cursor: pointer'
                                                                id='checkbox_option_{{ $option }}'
                                                                {{ $option_checked }}>
                                                            &nbsp; Activar /
                                                            Inactivar
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Monto:</label>
                                                                <input type='{{ $amount_type }}' class='form-control'
                                                                    style='display:block' id='amount_{{ $option }}'
                                                                    value="{{ $amount }}"
                                                                    {{ $amount_disabled }}></input>
                                                            </div>
                                                        </div>

                                                        @if ($option == 1)
                                                            <div class="col-md-4">
                                                                <label>Guardar este cambio:</label>
                                                                <button class="btn btn-default" title="Guardar este cambio."
                                                                    onclick='save({{ $parameters }}, 2)'>
                                                                    <span class="fa fa-save" aria-hidden="true"></span>
                                                                    &nbsp;
                                                                    Guardar
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        /**
         * Editar el estado de los teléfonos.
         */
        function save(parameters, type) {
            console.log('parameters', parameters);

            var status = null;

            if ($('#checkbox_option_' + parameters['option']).is(":checked")) {
                status = true;
            } else {
                status = false;
            }

            //En el caso del botón guarda el estado y el monto
            if (type == 2) {
                status = true;
            }

            var amount = $('#amount_' + parameters['option']).val();

            parameters['status'] = status;
            parameters['amount'] = amount;

            console.log('Monto:', amount);

            var url = '/service_rule_params/save';

            var json = {
                _token: token,
                parameters: parameters
            };

            $.post(url, json, function(data, status) {
                console.log('Status:', status);
                console.log('Data:', data);

                var status = data.status;
                var message = data.message;

                swal({
                        title: 'Atención',
                        text: message,
                        type: status,
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

        $(".delay_slide_up").delay(5000).slideUp(300);

        $("#amount_1").focus();
    </script>
@endsection
