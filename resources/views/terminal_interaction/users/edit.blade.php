@extends('layout')

@section('title')
    Usuarios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Editar Usuario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuarios</a></li>
            <li class="active">Editar Usuario</li>
        </ol>
    </section>
    <section class="content">

        <div class="delay_slide_up">
            @include('partials._flashes')
            @include('partials._messages')
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background:white">
                    <h4>Completar los campos</h4>

                    {!! Form::open(['route' => ['terminal_interaction_users_store'], 'method' => 'POST', 'role' => 'form', 'id' => 'form_user']) !!}
                    <div class="row">
                        <div class="col-md-12">
                            @include('terminal_interaction.users.partials.fields')
                        </div>
                    </div>

                    {!! Form::close() !!}

                    <button class="btn btn-danger" title="Cancelar y salir." id="cancel">
                        <span class="fa fa-times" aria-hidden="true"></span> &nbsp; Cancelar
                    </button>

                    &nbsp;

                    <button class="btn btn-primary" title="Guardar datos de usuario." id="save">
                        <span class="fa fa-save" aria-hidden="true"></span> &nbsp; Guardar
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background:white">
                    <h4>Horarios:</h4>
                    <div id="user_turns" style="border-spacing: 2px;">

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <!-- Iniciar -->
    <script type="text/javascript">
        //var user_id = "{{ $data['user_id'] }}";

        var data = <?php echo json_encode($data); ?>;
        //data = JSON.parse(data);
        console.log('Data:');

        /*<select class="form-control select2" id="record_limit" name="record_limit">
            <option value="" selected>Sin límite</option>
        </select>*/

        var turn_options = '<option value="" selected>Seleccionar turno</option>';

        $.each(data.turns, function(index, contents) {
            var d = contents;
            turn_options = turn_options + '<option value="' + d.id + '">' + d.description + '</option>';
        });

        $('#user_turns').append(
            $('<div class="row">')
            .append($('<div class="col-md-3">').append('Turno'))
            .append($('<div class="col-md-2">').append('Hora inicio'))
            .append($('<div class="col-md-2">').append('Hora fin'))
            .append($('<div class="col-md-2">').append('Estado'))
            .append($('<div class="col-md-1">').append('Opción'))
        );

        $.each(data.user_turns, function(index, contents) {
            console.log("d:", contents.description);

            d = contents;

            var select = $('<select>');
            select.attr({
                'class': 'form-control',
                'id': 'turn_' + contents.id
            });

            var status = d.status;
            status = (status) ? '1' : '0';

            var checked = (status == '1') ? 'checked' : '';

            select.html(turn_options);

            console.log('SELECT', select);

            $('#user_turns').append(
                $('<div class="row">').append($('<div class="col-md-3">').append(select))
                .append(
                    $('<div class="col-md-2">').append(
                        $('<input id="start_time_' + contents.id + '" class="form-control" type="time">')
                        .val(d.start_time)
                    )
                ).append(
                    $('<div class="col-md-2">').append(
                        $('<input id="end_time_' + contents.id + '" class="form-control" type="time">')
                        .val(d.end_time)
                    )
                ).append(
                    $('<div class="col-md-2">').append(
                        '<input type="checkbox" title="Activar/Inactivar" style="cursor: pointer" value="'+status+'" '+checked+'> &nbsp; Activar/Inactivar'
                    )
                ).append(
                    $('<div class="col-md-1">').append(
                        $('<button class="btn btn-default" title="Guardar.">').append(
                            '<span class="fa fa-save"></span>'
                        )
                    )
                )
            );

            $('#turn_' + contents.id).val(contents.id).trigger('change');
        });

        //data = data.user_turns;

        $("#save").click(function() {
            $("#form_user").submit();
        });

        $("#cancel").click(function() {
            location.href = "{{ route('terminal_interaction_users') }}";
        });

        $(".delay_slide_up").delay(5000).slideUp(300);
    </script>
@endsection
