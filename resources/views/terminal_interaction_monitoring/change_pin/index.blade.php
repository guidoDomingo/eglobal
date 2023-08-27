@extends('layout')

@section('title')
    Cambio de pin
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Cambio de pin
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Cambio de pin</a></li>
        </ol>
    </section>

    <section class="content">

        <style>
            .modal {
                background: rgba(0, 0, 0, 0.7);
            }

        </style>

        <?php
        $user_id = $data['user_id'];
        $login_status_checked = $data['status'] ? 'checked' : '';
        $pin_status = $data['pin_status'];
        
        $title_1 = 'Ingresar pin';
        $title_2 = 'Repetir pin';
        
        if ($pin_status == 'update') {
            $title_1 = 'Ingresar pin antiguo';
            $title_2 = 'Ingresar pin nuevo';
        }
        ?>

        <!-- Modal -->
        <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                style="width: 500px; background: white; border-radius: 5px">
                <!-- Modal content-->
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-header">
                        <div class="modal-title" style="font-size: 20px;">
                            Cambio de pin &nbsp; <small> <b> </b> </small>
                        </div>
                    </div>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            @include('partials._flashes')
                        </div>
                    </div>

                    @if ($pin_status == 'new' || $pin_status == 'update')
                        <div class="row">
                            <div class="col-md-12">
                                <label>{{ $title_1 }}</label>
                                <div class="form-group">
                                    <input type="password" class="form-control" name="pin" id="pin"></input>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label>{{ $title_2 }}</label>
                                <div class="form-group">
                                    <input type="password" class="form-control" name="pin_repeat" id="pin_repeat"></input>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        @if ($pin_status == 'new' || $pin_status == 'update')
                            <div class="col-md-4">
                                <button class="btn btn-primary" title="Buscar según los filtros en los registros."
                                    id="save" name="save" onclick="save()">
                                    <span class="fa fa-save"></span> &nbsp; Guardar
                                </button>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <button class="btn btn-danger" title="Salir de este formulario." id="exit" name="exit"
                                onclick="exit()">
                                <span class="fa fa-sign-out"></span> &nbsp; Salir
                            </button>
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

        function exit() {
            location.href = '/';
        }

        function save() {
            var pin = $('#pin').val();
            var pin_repeat = $('#pin_repeat').val();

            var url = "{{ route('terminal_interaction_monitoring_change_pin_edit') }}";

            var parameters = {
                'user_id': {{ $user_id }},
                'pin': pin,
                'pin_repeat': pin_repeat,
                'pin_status': "{{ $pin_status }}"
            };

            //console.log('parameters:', parameters);

            var json = {
                _token: token,
                parameters: parameters,
            };

            swal({
                    title: 'Atención',
                    text: '¿Está seguro de realizar esta acción?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                },
                function(isConfirmMessage) {
                    if (isConfirmMessage) {
                        $.post(url, json, function(data, status) {
                            var error = data.error;
                            var message = data.message;
                            var list = data.list;
                            var type = '';
                            var text = '';

                            if (error == true) {
                                type = 'error';
                                text = 'Ocurrió un error al realizar la acción.';
                            } else {
                                type = 'success';
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
                                function(isConfirmSearch) {
                                    if (isConfirmSearch) {
                                        if (error == true) {
                                            location.reload();
                                        } else {
                                            exit();
                                        }
                                    }
                                }
                            );
                        }).error(function(error) {
                            console.log('Error al realizar la acción:', error);
                        });
                    }
                }
            );
        }

        $('#modal').modal('show');
    </script>
@endsection
