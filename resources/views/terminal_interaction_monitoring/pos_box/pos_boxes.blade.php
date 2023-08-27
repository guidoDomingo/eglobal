<?php
$login_status_checked = $pos_boxes['status'] ? 'checked' : '';
?>

<div class="row">
    <div class="col-md-12">
        <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
            <label>Login en terminal:</label> <br />
            <input type='checkbox' title='Puede realizar login en terminales'
                onclick='terminal_interaction_login_add({{ $user->id }})' style='cursor: pointer;'
                {{ $login_status_checked }}>
            &nbsp; Activar /
            Inactivar
        </div>
    </div>
</div>

@if ($login_status_checked)
    @if (count($pos_boxes['list']) > 0)
        <?php
        $groups = array_keys($pos_boxes['list']);
        ?>

        @foreach ($groups as $group)
            <?php
            $group = $pos_boxes['list'][$group];
            $group_id = $group['group_id'];
            $group_description = $group['group_description'];
            $group_supervisor = $group['group_supervisor'];
            $updated_at = $group['updated_at'];
            $checkted = $group_supervisor ? 'checked' : '';
            $group_supervisor = $group_supervisor ? 'si' : 'no';
            $branches = $group['branch_list'];
            
            $parameters = [
                'update_type' => 'group',
                'referential_id' => $group_id,
                'supervisor' => $group_supervisor,
                'user_id' => $pos_boxes['user_id'],
            ];
            
            $parameters = json_encode($parameters);
            ?>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"> {{ $group_description }} </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Última actualización:</label>
                                <p class="help-block">{{ $updated_at }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>Supervisor:</label> <br />
                            <input type='checkbox' title='Asignar como supervisor'
                                onclick="terminal_interaction_access_edit({{ $parameters }})" style='cursor: pointer;'
                                {{ $checkted }}> &nbsp; Activar /
                            Inactivar
                        </div>
                    </div>

                    <hr />

                    <div class="row">
                        @foreach ($branches as $branch)

                            <?php
                            $branch_id = $branch['branch_id'];
                            $branch_description = $branch['branch_description'];
                            $branch_supervisor = $branch['branch_supervisor'];
                            $updated_at = $branch['updated_at'];
                            $checkted = $branch_supervisor ? 'checked' : '';
                            $branch_supervisor = $branch_supervisor ? 'si' : 'no';
                            $atms = $branch['atm_list'];
                            
                            $parameters = [
                                'update_type' => 'branch',
                                'referential_id' => $branch_id,
                                'supervisor' => $branch_supervisor,
                                'user_id' => $pos_boxes['user_id'],
                            ];
                            
                            $parameters = json_encode($parameters);
                            ?>

                            <div class="col-md-6">
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"> {{ $group_description }} / {{ $branch_description }}
                                        </h3>
                                        <div class="box-tools pull-right">
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                                <i class="fa fa-minus"></i>
                                            </button>
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
                                                <label>Encargado:</label> <br />
                                                <input type='checkbox' title=''
                                                    onclick="terminal_interaction_access_edit({{ $parameters }})"
                                                    style='cursor: pointer;' {{ $checkted }}> &nbsp; Activar /
                                                Inactivar
                                            </div>
                                        </div>

                                        <hr />

                                        <div class="row">
                                            @foreach ($atms as $atm)

                                                <?php
                                                $atm_id = $atm['atm_id'];
                                                $atm_description = $atm['atm_description'];
                                                $atm_supervisor = $atm['atm_supervisor'];
                                                $updated_at = $atm['updated_at'];
                                                $checkted = $atm_supervisor ? 'checked' : '';
                                                $atm_supervisor = $atm_supervisor ? 'si' : 'no';
                                                
                                                $pos_box_id = $atm['pos_box_id'];
                                                $pos_box_id_fk = $atm['pos_box_id_fk'];
                                                $status = $atm['status'];
                                                
                                                $checkted_status = '';
                                                
                                                if ($pos_box_id_fk !== null) {
                                                    $checkted_status = $status ? 'checked' : '';
                                                }
                                                
                                                $parameters = [
                                                    'update_type' => 'atm',
                                                    'referential_id' => $atm_id,
                                                    'supervisor' => $atm_supervisor,
                                                    'user_id' => $pos_boxes['user_id'],
                                                    'pos_box_id' => $pos_box_id,
                                                ];
                                                
                                                $parameters = json_encode($parameters);
                                                ?>

                                                <div class="col-md-12">
                                                    <div class="callout callout-default"
                                                        style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                                        <h4> {{ $atm_description }} - Terminal N°
                                                            {{ $atm_id }}
                                                        </h4>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Asignar ATM:</label> <br />
                                                                <input type='checkbox'
                                                                    title='Asignar atm para realizar movimientos'
                                                                    onclick='terminal_interaction_assign_atm({{ $parameters }})'
                                                                    style='cursor: pointer;' {{ $checkted_status }}>
                                                                &nbsp; Activar /
                                                                Inactivar
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Encargado:</label> <br />
                                                                <input type='checkbox' title='Asignar como supervisor'
                                                                    onclick='terminal_interaction_access_edit({{ $parameters }})'
                                                                    style='cursor: pointer;' {{ $checkted }}>
                                                                &nbsp; Activar /
                                                                Inactivar
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-danger" role="alert">
            No hay cajas disponibles para este usuario.
        </div>
    @endif
@else
    <div class="alert alert-danger" role="alert">
        El usuario no está habilitado para hacer login en terminales.
    </div>
@endif


@section('js')
    <script type="text/javascript">

        function confirm_message_action(url, json) {
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
                                        location.reload();
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

        function terminal_interaction_login_add(user_id) {
            var url = "{{ route('terminal_interaction_login_add') }}";
            var json = {
                _token: token,
                user_id: user_id
            };

            confirm_message_action(url, json);
        }

        function terminal_interaction_assign_atm(parameters) {
            var url = "{{ route('terminal_interaction_assign_atm') }}";

            var json = {
                _token: token,
                parameters: parameters
            };

            confirm_message_action(url, json);
        }


        function terminal_interaction_access_edit(parameters) {

            var url = "{{ route('terminal_interaction_access_edit') }}";

            var json = {
                _token: token,
                parameters: parameters
            };

            confirm_message_action(url, json);
        }
    </script>
@endsection
