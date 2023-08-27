@extends('layout')

@section('title')
    Usuarios
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Agregar Usuario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuarios</a></li>
            <li class="active">Agregar Usuario</li>
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

                    <div class="row">
                        {!! Form::open(['route' => ['terminal_interaction_users_store'], 'method' => 'POST', 'role' => 'form', 'id' => 'form_user']) !!}
                        @include('terminal_interaction.users.partials.fields')
                        {!! Form::close() !!}

                        <div class="col-md-9"></div>

                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <button class="btn btn-danger" title="Cancelar y salir." id="cancel">
                                        <span class="fa fa-times" aria-hidden="true"></span> &nbsp; Cancelar
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-primary" title="Guardar datos de usuario." id="save">
                                        <span class="fa fa-save" aria-hidden="true"></span> &nbsp; Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page_scripts')
    @include('partials._selectize')
@endsection

@section('js')
    <!-- Iniciar -->
    <script type="text/javascript">
        $("#save").click(function() {
            $("#form_user").submit();
        });

        $("#cancel").click(function() {
            location.href = "{{ route('terminal_interaction_users') }}";
        });

        $(".delay_slide_up").delay(5000).slideUp(300);

        function validate_password() {
            var b = false;

            if ($("#password").val() == $("#password_2").val()) {
                b = true;
            }

            if (b) {
                //Verde
                $("#password").css({
                    'border-color': '#008d4c'
                });
                $("#password_2").css({
                    'border-color': '#008d4c'
                });

                $("#label_password").text(' (Iguales)');
                $("#label_password_2").text(' (Iguales)');
            } else {
                //Rojo
                $("#password").css({
                    'border-color': '#d73925'
                });
                $("#password_2").css({
                    'border-color': '#d73925'
                });

                $("#label_password").text(' (Diferentes)');
                $("#label_password_2").text(' (Diferentes)');
            }
        }

        $("#password").keyup(function(event) {
            validate_password();
        });

        $("#password_2").keyup(function(event) {
            validate_password();
        });

        

        //console.log('inputs:', inputs);

        $('#role_id').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            render: {
                item: function(item, escape) {
                    return '<div><span class="label label-primary">' + escape(item.name) + '</span></div>';
                }
            },
            options: {!! $data['roles'] !!}
        });

        $('#branch_id').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['branches'] !!}
        });

        var inputs = {!! $data['inputs'] !!};

        if (inputs !== null) {
            $("#description").val(inputs.description);
            $("#username").val(inputs.username);
            $("#password").val(inputs.password);
            $("#password_2").val($("#password").val());
            $("#doc_number").val(inputs.doc_number);
            $("#email").val(inputs.email);
            //$("#branch_id").val(inputs.branch_id);
            //$("#role_id").val(inputs.role_id);

            $('#branch_id').selectize()[0].selectize.setValue(inputs.branch_id, false);
            $('#role_id').selectize()[0].selectize.setValue(inputs.role_id, false);
        }
    </script>
@endsection
