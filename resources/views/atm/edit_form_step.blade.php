@extends('layout')

@section('title')
    Nuevo ATM
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATM
            <small>Edición de ATM</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Atms</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        {{-- step headers --}}
        <div class="row">
            <div class="col-md-12">
                <div class="container">
                    <div class="stepwizard">
                        <div class="stepwizard-row setup-panel">
                            <div class="stepwizard-step col-xs-3"> 
                                <button href="#step-1" type="button" class="btn btn-success btn-circle">1</button>
                                <p>ATM</p>
                            </div>
                            <div class="stepwizard-step col-xs-3"> 
                                <button href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</button>
                                <p>Puntos de Venta</p>
                            </div>
                            <div class="stepwizard-step col-xs-3"> 
                                <button href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</button>
                                <p>Contabilidad</p>
                            </div>
                            <div class="stepwizard-step col-xs-3"> 
                                <button href="#step-4" type="button" class="btn btn-default btn-circle" disabled="disabled">4</button>
                                <p>Aplicación</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--  --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary setup-content" id="step-1">
                    <div class="overlay"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Editar ATM</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::model($atm,['route' => ['atm.update', 'id' => $atm->id] , 'method' => 'PUT', 'role' => 'form', 'id' => 'nuevoAtm-form']) !!}
                        @include('atm.partials.step_fields_1')
                        <a class="btn btn-default cancelar" href="{{ route('atm.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardarAtm">Siguiente</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box box-primary setup-content" id="step-2">
                    <div class="overlay"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Editar Punto de Venta - <span id='labelRed'></span></h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        @if(empty($pointofsale))
                            {!! Form::open(['route' => 'pos.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoPos-form']) !!}
                        @else
                            {!! Form::model($pointofsale,['route' => ['pos.update', $pointofsale->id ] , 'method' => 'PUT', 'role' => 'form', 'id' => 'nuevoPos-form']) !!}
                            {!! Form::hidden('id',$pointofsale->id) !!}
                            {!! Form::hidden('atm_id',$atm->id) !!}
                        @endif
                        @include('pos.partials.step_fields_2')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default atras" href="#step-1" role="button">Atras</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardarPos">Siguiente</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="box box-primary setup-content" id="step-3">
                    <div class="overlay"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Editar Comprobante - PDV <span id='labelPdv'></span></h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        @if(empty($posVoucher))
                            {!! Form::open(['route' => ['pointsofsale.vouchers.store', 0] , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoComprobante-form']) !!}
                        @else
                            {!! Form::model($posVoucher,['route' => ['pointsofsale.vouchers.update', $posVoucher->point_of_sale_id,$posVoucher->id] , 'method' => 'PUT', 'role' => 'form', 'id' => 'nuevoComprobante-form']) !!}
                            {!! Form::hidden('id',$posVoucher->id) !!}

                        @endif
                        @include('posvouchers.partials.step_fields_3')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default atras" href="#step-2" role="button">Atras</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardarComprobante">Siguiente</button>
                        <button class="btn btn-primary" id="btnOmitirPdv">Omitir</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="box box-primary setup-content" id="step-4">
                    <div class="overlay"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Asignar Aplicación</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        @if($atm->owner_id == 16 || $atm->owner_id == 21 || $atm->owner_id == 25)
                                {!! Form::open(['route' => ['groups.update_branch', $pointofsale->branch_id] , 'method' => 'PUT', 'role' => 'form', 'id' => 'nuevogrupo-form']) !!}
                                @include('groups.partials.step_fields_5')
                                {!! Form::close() !!}
                        @endif
                        {!! Form::open(['route' => ['applications.assign_atm', $app_id ] , 'method' => 'POST', 'role' => 'form', 'id' => 'asignarAplicacion-form']) !!}
                        @include('atm.partials.step_fields_4')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default atras" href="#step-3" role="button">Atras</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardarAplicacion">Finalizar</button>
                    </div>
                    {!! Form::close() !!}
                </div>
                @include('atm.partials.generate_hash')

            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
    <script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            var atmStatus = '{{$atm->atm_status}}';
            $('#atmId').val({{$atm->id}});
            $('.overlay').hide();
            $('#valid_from').datepicker({
                language: 'es',
                format: 'dd/mm/yyyy',
            });

            $('#valid_until').datepicker({
                language: 'es',
                format: 'dd/mm/yyyy',
            });
            
            // validacion del modal de nueva red
            $('#nuevaRed-form').validate({
                rules: {
                    "name": {
                        required: true,
                    }
                },
                messages: {
                    "name": {
                        required: "Ingrese un nombre",
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('#load').toggleClass('active');
                    $(form).find('input[name="name"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('#load').toggleClass('active');
                        $(form).find('input[name="name"]').val('').prop('readonly',false);
                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }
                        $('#modalNuevaRed').modal('hide');
                        var newOption = new Option(respuesta.data.name, respuesta.data.id, false, true);
                        $('#owner_id').append(newOption).trigger('change');
                    });
                }
            });

            //validacion formulario atm
            $('#nuevoAtm-form').validate({
                rules: {
                    "owner_id": {
                        required: true,
                    },
                    "name": {
                        required: true,
                    },
                    "code": {
                        required: true,
                        number: true,
                        remote: {
                            url: "{{ url('atm/check_code') }}",
                            type: "get",
                            data:{
                                code: function(){
                                    return $('#code').val();
                                },
                                id: function(){
                                    return $('#id').val();
                                }
                            }
                            
                        }
                    },
                },
                messages: {
                    "owner_id": {
                        required: "Seleccione la red",
                    },
                    "name": {
                        required: "Ingrese un nombre",
                    },
                    "code": {
                        required: "Ingrese un código",
                        number: "Ingrese solo valores numéricos",
                        remote: "El código ingresado ya existe"
                    },
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('.overlay').show();
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('.overlay').hide();
                        $(form).find('input[type="text"]').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        if(!$('#nuevoAtm-form #id').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'id',
                                value: respuesta.data.id,
                                id: 'id'
                            }).appendTo(form);

                            $('<input>').attr({
                                type: 'hidden',
                                name: '_method',
                                value: 'PUT'
                            }).appendTo(form);
                        }

                        $(form).attr('action',respuesta.url);

                        //agregando valores al step new pos
                        if(!$('#nuevoPos-form #pos_owner_id').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'owner_id',
                                id: 'pos_owner_id',
                                value: $('#owner_id').val()
                            }).appendTo('#nuevoPos-form');
                        }

                        if(!$('#nuevoPos-form #pos_atm_id').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'atm_id',
                                id: 'pos_atm_id',
                                value: respuesta.data.id
                            }).appendTo('#nuevoPos-form');
                        }

                        $('#labelRed').html($('#owner_id option:selected').text());
                        var urlNuevaSucursal = $('#nuevaSucursal-form').attr('action');
                        $('#nuevaSucursal-form').attr('action',urlNuevaSucursal.replace('0',$('#owner_id').val()));

                        $('#atmId').val(respuesta.data.id);
                        /*$('#aplicacionId').empty().trigger('change');
                        $('#aplicacionId').select2({data: respuesta.data.applications});*/

                        nextStep('#btnGuardarAtm');
                    });
                }
            });

            //validacion formulario pos
            $('#nuevoPos-form').validate({
                rules: {
                    "branch_id": {
                        required: true,
                    },
                    "pos_code": {
                        required: true,
                        number: true,
                        min: 1,
                        max: 99999
                    },
                    "ondanet_code": {
                        required: true,
                    },
                    "description": {
                        required: true,
                        maxlength: 255
                    },
                },
                messages: {
                    "branch_id": {
                        required: "Seleccione la sucursal",
                    },
                    "pos_code": {
                        required: "Ingrese el código de sucursal",
                        number: 'Ingrese solo valores numéricos',
                        min: 'El numero minimo para el codigo del punto es 1',
                        max: 'El numero maximo para le codigo del punto es 99999',
                    },
                    "ondanet_code": {
                        required: "Ingrese el código de depósito",
                    },
                    "description": {
                        required: "Ingrese el nombre",
                    },
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('.overlay').show();
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('.overlay').hide();
                        $(form).find('input[type="text"]').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        if(!$(form).find('input[name="id"]').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'id',
                                value: respuesta.data.id
                            }).appendTo(form);

                            $('<input>').attr({
                                type: 'hidden',
                                name: '_method',
                                value: 'PUT'
                            }).appendTo(form);
                        }
                        $(form).attr('action',respuesta.url);

                        //agregando valores al step new pos
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'owner_id',
                            value: $('#owner_id').val()
                        }).appendTo('#nuevoComprobante-form');

                        $('#labelPdv').html($('#nuevoPos-form input[name=description]').val());
                        var urlNuevoTipo = $('#nuevoTipo-form').attr('action');
                        var urlNuevoComprobante = $('#nuevoComprobante-form').attr('action');
                        $('#nuevoTipo-form').attr('action',urlNuevoTipo.replace('0',respuesta.data.id));

                        if(!$('#nuevoComprobante-form input[name="id"]').length){
                            $('#nuevoComprobante-form').attr('action',urlNuevoComprobante.replace('0',respuesta.data.id));
                        }

                        nextStep('#btnGuardarPos');
                    });
                }
            });

            // validacion del modal de nueva sucursal
            $('#nuevaSucursal-form').validate({
                rules: {
                    "description": {
                        required: true,
                    },
                    "branch_code": {
                        required: true,
                    },
                    "address": {
                        required: true,
                    },
                    "phone": {
                        required: true,
                    },
                    "user_id": {
                        required: true,
                    },
                    "latitud": {
                        required: true,
                    },
                    "longitud": {
                        required: true,
                    },
                },
                messages: {
                    "description": {
                        required: "Ingrese un nombre",
                    },
                    "branch_code": {
                        required: "Ingrese un codigo de sucursal",
                    },
                    "address": {
                        required: "Ingrese la dirección",
                    },
                    "phone": {
                        required: "Ingrese el teléfono",
                    },
                    "user_id": {
                        required: "Seleccione el responsable",
                    },
                    "latitud": {
                        required: "Ingrese la latitud",
                    },
                    "longitud": {
                        required: "Ingrese la longitud",
                    },
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('#btnGuardarSucursal').toggleClass('active');
                    $(form).find('input[type="text"]').prop('readonly',true);
                    $(form).find('input[type="select"]').prop('readonly',true);
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('#btnGuardarSucursal').toggleClass('active');
                        $(form).find('input[type="text"]').val('').prop('readonly',false);
                        $(form).find('input[type="select"]').val('').prop('readonly',false);
                        
                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        //agregando valores al step new pos
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'branch_id',
                            value: $('#branch_id').val()
                        }).appendTo('#nuevogrupo-form');

                        var urlNuevoGroup = $('#nuevogroup-form').attr('action');
                        var urlNuevoGrupo = $('#nuevogrupo-form').attr('action');
                        $('#nuevogroup-form').attr('action',urlNuevoGroup.replace('0',respuesta.data.id));

                        if(!$('#nuevogrupo-form input[name="id"]').length){
                            $('#nuevogrupo-form').attr('action',urlNuevoGrupo.replace('0',respuesta.data.id));
                        }

                        $('#modalNuevaSucursal').modal('hide');
                        var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                        $('#branch_id').append(newOption).trigger('change');
                    });
                }
            });

            // validacion de nuevo comprobante pdv 
            $('#nuevogrupo-form').validate({
                rules: {
                    "description": {
                        required: true,
                    },
                    "ruc": {
                        required: false,
                    }
                },
                messages: {
                    "description": {
                        required: "Ingrese un nombre",
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('.overlay').show();
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('.overlay').hide();
                        $(form).find('input[type="text"]').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }
                        
                        if(!$('#nuevogrupo-form #group_id').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'group_id',
                                value: respuesta.data.id,
                                id: 'group_id'
                            }).appendTo(form);

                            $('<input>').attr({
                                type: 'hidden',
                                name: '_method',
                                value: 'PUT'
                            }).appendTo(form);
                        }

                        //agregando valores al step new pos
                        if(!$('#nuevogroup-form #group_id').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'group_id',
                                id: 'group_id',
                                value: $('#group_id').val()
                            }).appendTo('#nuevogroup-form');
                        }
                        
                        $(form).attr('action',respuesta.url);

                        var urlNuevoGrupo = $('#nuevogroup-form').attr('action');
                        $('#nuevogroup-form').attr('action',urlNuevoGrupo.replace('0',$('#group_id').val()));
                        
                        $('#labelGrupo').html($('#group_id option:selected').text());
                    });
                }
            });

            //al seleccionar departamento
            $(document).on('select2:select','#departamento_id',function(){
                $.get("{{ route('atm.ciudades') }}", {departamento_id: $(this).val()}).done(function(response){
                    $('#ciudad_id').html(response).trigger( 'change');
                });
            })

            //al seleccionar ciudad
            $(document).on('select2:select','#ciudad_id',function(){
                $.get("{{ route('atm.barrios') }}", {ciudad_id: $(this).val()}).done(function(response){
                    $('#barrio_id').html(response).trigger( 'change');
                });
            })

            // validacion del modal de nueva red
            $('#nuevoTipo-form').validate({
                rules: {
                    "voucher_type_id": {
                        required: true,
                    },
                },
                messages: {
                    "voucher_type_id": {
                        required: "Seleccione un tipo",
                    },
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('#load').toggleClass('active');
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('#load').toggleClass('active');
                        $(form).find('input[name="name"]').val('').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        $('#modalNuevoTipo').modal('hide');
                        var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                        $('#pos_voucher_type_id').append(newOption).trigger('change');
                    });
                }
            });

            // validacion de nuevo comprobante pdv 
            $('#nuevoComprobante-form').validate({
                rules: {
                    "pos_voucher_type_id": {
                        required: true,
                    },
                    "stamping": {
                        required: true,
                    },
                    "from_number": {
                        required: true,
                    },
                    "to_number": {
                        required: true,
                    },
                    "valid_from": {
                        required: true,
                    },
                    "valid_until": {
                        required: true,
                    }
                },
                messages: {
                    "pos_voucher_type_id": {
                        required: "Seleccione un tipo",
                    },
                    "stamping": {
                        required: "Ingrese el timbrado",
                    },
                    "from_number": {
                        required: "Ingrese la numeración desde",
                    },
                    "to_number": {
                        required: "Ingrese la numeración hasta",
                    },
                    "valid_from": {
                        required: "Ingrese la fecha desde",
                    },
                    "valid_until": {
                        required: "Ingrese la fecha hasta",
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('.overlay').show();
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('.overlay').hide();
                        $(form).find('input[type="text"]').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        if(!$(form).find('input[name="id"]').length){
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'id',
                                value: respuesta.data.id
                            }).appendTo(form);

                            $('<input>').attr({
                                type: 'hidden',
                                name: '_method',
                                value: 'PUT'
                            }).appendTo(form);
                        }
                        $(form).attr('action',respuesta.url);
                        nextStep('#btnGuardarComprobante');
                    });
                }
            });

            // validacion de nuevo comprobante pdv 
            $('#asignarAplicacion-form').validate({
                rules: {
                    "application_id": {
                        required: true,
                    },
                    "tipo_dispositivo": {
                        required: true,
                    },
                },
                messages: {
                    "application_id": {
                        required: "Seleccione la aplicacion",
                    },
                    "tipo_dispositivo": {
                        required: "Seleccione el tipo de dispositivo",
                    },
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('.overlay').show();
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('.overlay').hide();

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }
                        
                        $('#reasignar').val(respuesta.reasignar);

                        $('#atmName').text(respuesta.resumen.name);
                        $('#atmCode').text(respuesta.resumen.code);
                        $('#atmOwner').text(respuesta.resumen.owner_name);
                        $('#posName').text(respuesta.resumen.pos_name);
                        $('#posBranch').text(respuesta.resumen.branch_name);
                        $('#posOndanetCode').text(respuesta.resumen.ondanet_code);
                        $('#posCode').text(respuesta.resumen.pos_code);
                        
                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#tipoComprobante').text(respuesta.resumen.description);
                        }

                        if(typeof respuesta.resumen.grupo != 'undefined'){
                            $('#grupo').text(respuesta.resumen.grupo);
                        }

                        if(typeof respuesta.resumen.ruc != 'undefined'){
                            $('#ruc').text(respuesta.resumen.ruc);
                        }
                        
                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#timbrado').text(respuesta.resumen.stamping);
                        }
                        
                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#numeracionDesde').text(respuesta.resumen.from_number);
                        }
                        
                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#numeracionHasta').text(respuesta.resumen.to_number);
                        }

                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#validoDesde').text(respuesta.resumen.valid_from);
                        }

                        if(typeof respuesta.resumen.description != 'undefined'){
                            $('#validoHasta').text(respuesta.resumen.valid_until);
                        }
                        
                        $('#aplicacion').text(respuesta.resumen.application_name);
                        $('#modalResumen').modal('show');

                        $(document).on('click','#bntConfirmarResumen',function(){
                            $('#modalResumen').modal('hide');
                            $(form).find('input[type="text"]').prop('readonly',false);
                            swal({
                                title: respuesta.titulo,
                                text: respuesta.mensaje,
                                html: respuesta.mensaje,
                                type: respuesta.tipo,
                                closeOnClickOutside: false
                            }, function(){
                                window.location = '{{ url('atm') }}?id='+$('#nuevoAtm-form input[name=id]').val();
                            });
                        })
                    });
                }
            });

            $('#aplicacionId').on('select2:select',function(){
                var url = $('#asignarAplicacion-form').attr('action');

                if(url.search('applications//') != -1){
                    url = url.replace('applications//','applications/0/');
                }else if(url.search('applications/null/') != -1){
                    url = url.replace('applications/null/','applications/0/');
                }else{
                    url = url.replace(/\d+/g,'0');
                }

                $('#asignarAplicacion-form').attr('action',url.replace('0',$('#aplicacionId').val()));
            });

            $('#group_id').on('select2:select',function(){
                var url = $('#nuevogrupo-form').attr('action');

                console.log(url);
                console.log($('#group_id').val());

                $('#nuevogrupo-form').attr('action',url.replace('0',$('#group_id').val()));


                var access_token = "{{csrf_token()}}";
                var data = {
                    _token: access_token,
                    group_id: $('#group_id').val(),
                    atm_id: $('#atm_id').val()
                };
                
                $.ajax({
                type: 'PUT',
                url: url,
                data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
                success: function(result) {console.log(result)}
                });
                
            });

            $(document).on('click','#btnOmitirPdv', function(e){
                e.preventDefault();
                nextStep('#btnGuardarComprobante');
            });

            // validacion del modal de nueva sucursal
            $('#nuevogroup-form').validate({
                rules: {
                    "description": {
                        required: true,
                    },
                    "ruc": {
                        required: false,
                    }
                },
                messages: {
                    "description": {
                        required: "Ingrese un nombre",
                    },
                    "ruc": {
                        required: "Ingrese un RUC"
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent());
                },
                submitHandler: function(form) {
                    $('#load').toggleClass('active');
                    $(form).find('input[type="text"]').prop('readonly',true)
                    $.post(form.action, $(form).serialize()).done(function(respuesta){
                        $('#load').toggleClass('active');
                        $(form).find('input[name="name"]').val('').prop('readonly',false);

                        if(respuesta.tipo == 'error'){
                            var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                            $(form).find('input[type="text"]').prop('readonly',false)
                            return new PNotify({
                                title: "Atención",
                                text: respuesta.mensaje,
                                addclass: "stack-custom",
                                stack: myStack,
                                type: respuesta.tipo
                            });
                        }

                        $('#modalNuevoGrupo').modal('hide');
                        var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                        console.log(newOption);
                        $('#group_id').append(newOption).trigger('change');
                    });
                }
            });

            // Funcion que genera el formulario por pasos
            var navListItems = $('div.setup-panel div button'),
                    allWells = $('.setup-content'),
                    allNextBtn = $('.nextBtn');
            formSteps(); //funcion que se encarga de generar el formulario por pasos
            function formSteps(){
                allWells.hide();

                navListItems.click(function (e) {
                    e.preventDefault();
                    var $target = $($(this).attr('href')),
                        $item = $(this);

                    if (!$item.hasClass('disabled')) {
                        navListItems.removeClass('btn-success').addClass('btn-default');
                        $item.addClass('btn-success');
                        allWells.hide();
                        $target.show();
                        $target.find('input:eq(0)').focus();
                    }
                });

                $('div.setup-panel div a.btn-success').trigger('click');
            }

            function nextStep(btnId) {
                var curStep = $(btnId).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextStepWizard = $('div.setup-panel div button[href="#' + curStepBtn + '"]').parent().next().children("button"),
                    curInputs = curStep.find("input[type='text'],input[type='url']"),
                    isValid = true;

                $(".form-group").removeClass("has-error");
                for (var i = 0; i < curInputs.length; i++) {
                    if (!curInputs[i].validity.valid) {
                        isValid = false;
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }

                if (isValid) nextStepWizard.removeAttr('disabled').trigger('click');
            };

            $(document).on('click','.atras', function(){
                var curStep = $(this).closest(".setup-content"),
                    curStepBtn = curStep.attr("id"),
                    nextStepWizard = $('div.setup-panel div button[href="#' + curStepBtn + '"]').parent().prev().children("button"),
                    curInputs = curStep.find("input[type='text'],input[type='url']"),
                    isValid = true;

                $(".form-group").removeClass("has-error");
                for (var i = 0; i < curInputs.length; i++) {
                    if (!curInputs[i].validity.valid) {
                        isValid = false;
                        $(curInputs[i]).closest(".form-group").addClass("has-error");
                    }
                }

                if (isValid) nextStepWizard.removeAttr('disabled').trigger('click');
            });

            $('button[href="#step-1"]').trigger('click');

            if(atmStatus.search('-') != -1){
                $('button[href="#step'+atmStatus+'"]').removeAttr('disabled').trigger('click');
            }

            $(window).on("beforeunload", function() { 
                return ""; 
            });
        });

    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        /* Latest compiled and minified CSS included as External Resource*/
        /* Optional theme */

        /*@import url('//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css');*/
        .stepwizard-step p {
            margin-top: 0px;
            color:#666;
        }
        .stepwizard-row {
            display: table-row;
        }
        .stepwizard {
            display: table;
            width: 100%;
            position: relative;
        }
        .stepwizard-step button[disabled] {
            /*opacity: 1 !important;
            filter: alpha(opacity=100) !important;*/
        }
        .stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
            opacity:1 !important;
            color:#bbb;
        }
        .stepwizard-row:before {
            top: 14px;
            bottom: 0;
            position: absolute;
            content:" ";
            width: 100%;
            height: 1px;
            background-color: #ccc;
            z-index: 0;
        }
        .stepwizard-step {
            display: table-cell;
            text-align: center;
            position: relative;
        }
        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }

        /* animacion del boton al guardar */
        .spinner {
          display: inline-block;
          opacity: 0;
          width: 0;

          -webkit-transition: opacity 0.25s, width 0.25s;
          -moz-transition: opacity 0.25s, width 0.25s;
          -o-transition: opacity 0.25s, width 0.25s;
          transition: opacity 0.25s, width 0.25s;
        }

        .has-spinner.active {
          cursor:progress;
        }

        .has-spinner.active .spinner {
          opacity: 1;
          width: auto; /* This doesn't work, just fix for unkown width elements */
        }

        .has-spinner.btn-mini.active .spinner {
            width: 10px;
        }

        .has-spinner.btn-small.active .spinner {
            width: 13px;
        }

        .has-spinner.btn.active .spinner {
            width: 16px;
        }

        .has-spinner.btn-large.active .spinner {
            width: 19px;
        }

    </style>
@endsection
@include('atm.partials.modal_owner')
@include('atm.partials.modal_vouchers_type')
@include('atm.partials.modal_branch')
@include('atm.partials.modal_resume')
@include('atm.partials.modal_group')
