
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
<script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
{{-- <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script> --}}
<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    if($('#reservationtime').val() == '' || $('#reservationtime').val() == 0){
        var date = new Date();
        var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear();
        var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear();

        $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
    }
    //Date range picker
    $('#reservationtime').daterangepicker({
        opens: 'right',
        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie','Sab'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        },
        format: 'DD/MM/YYYY',
        startDate: moment(),
        endDate: moment().add(12,'months'),
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        
        //Script para superposicion de modales de 3 niveles
        // Start script
        var $body = $('body');
        var OPEN_MODALS_COUNT = 'fv_open_modals';
        var Z_ADJUSTED = 'fv-modal-stack';
        var defaultBootstrapModalZindex = 1040;

        // keep track of the number of open modals                   
        if ($body.data(OPEN_MODALS_COUNT) === undefined) {
            $body.data(OPEN_MODALS_COUNT, 0);
        }

        $body.on('show.bs.modal', '.modal', function (event)
        {
            if (!$(this).hasClass(Z_ADJUSTED))  // only if z-index not already set
            {
                // Increment count & mark as being adjusted
                $body.data(OPEN_MODALS_COUNT, $body.data(OPEN_MODALS_COUNT) + 1);
                $(this).addClass(Z_ADJUSTED);

                // Set Z-Index
                $(this).css('z-index', defaultBootstrapModalZindex + (1 * $body.data(OPEN_MODALS_COUNT)));

                //// BackDrop z-index   (Doesn't seem to be necessary with Bootstrap 3.3.2 ...)
                //$('.modal-backdrop').not( '.' + Z_ADJUSTED )
                //        .css('z-index', 1039 + (10 * $body.data(OPEN_MODALS_COUNT)))
                //        .addClass(Z_ADJUSTED);
            }
        });
        $body.on('hidden.bs.modal', '.modal', function (event)
        {
            // Decrement count & remove adjusted class
            $body.data(OPEN_MODALS_COUNT, $body.data(OPEN_MODALS_COUNT) - 1);
            $(this).removeClass(Z_ADJUSTED);
            // Fix issue with scrollbar being shown when any modal is hidden
            if($body.data(OPEN_MODALS_COUNT) > 0)
                $body.addClass('modal-open');
        });
        // End script

        //separador de miles - limite de credito | Contratos
        // var separador = document.getElementById('credit_limit_contract');
        //         separador.addEventListener('input', (e) => {
        //             var entrada = e.target.value.split(','),
        //             parteEntera = entrada[0].replace(/\./g, ''),
        //             salida = parteEntera.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //             e.target.value = salida;
        //         }, true);

        // //separador de miles - Capital de la poliza
        // var separadorPol = document.getElementById('capital_poliza');

        // separadorPol.addEventListener('input', (e) => {
        //     var entradaPol = e.target.value.split(','),
        //     parteEnteraPol = entradaPol[0].replace(/\./g, ''),
        //     salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //     e.target.value = salidaPol;
        // }, false);



        $('.select2').select2();

        $('.overlay').hide();

        $('.reservationtime').datepicker({
            changeMonth: true,
            changeYear: true,
            language: 'es',
            format: 'yyyy/mm/dd',
            firstDay: 1
        }); 
        
        $('#valid_from').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        $('#valid_until').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });
        
        $('#date_init').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        $('#date_end').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        $('#installation_date').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        $('#date_init_network').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        $('#date_end_network').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });
        ///////  PASO 1: AREA - COMERCIAL   ////////
        ///TAB 1 - ATM
        // Valiaciones del Modal Nueva red
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#load').toggleClass('active');
                $(form).find('input[name="name"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#load').toggleClass('active');
                    $(form).find('input[name="name"]').val('').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
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

        $("#owner_id").change(function(){
            var owner = $("#owner_id").val();
            if (owner == 16){
                document.getElementById("grilla").style.display = "block";
            }else{
                document.getElementById("grilla").style.display = "none";
            }
        });

        //Validaciones del formulario atm
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
                    remote: "{{ url('atm/new/check_code') }}"
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $(form).find('#owner_id').prop('disabled', true)
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    //Insertar el mismo nombre del atm a la descripcion del punto de venta
                    $('#description_sucursal').val(respuesta.atm_name);
                    //Insertar codigo identificador para el numero de contrato
                    // $('#number_contract').val(respuesta.);

                    if (!$('#nuevoAtm-form #id').length) {
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

                    $(form).attr('action', respuesta.url);

                    //agregando valores al step new pos
                    if (!$('#nuevoPos-form #pos_owner_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'owner_id',
                            id: 'pos_owner_id',
                            value: $('#owner_id').val()
                        }).appendTo('#nuevoPos-form');
                    }

                    if (!$('#nuevoPos-form #pos_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'pos_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevoPos-form');
                    }

                    
                    if (!$('#nuevaSucursal-form #branches_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'branches_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevaSucursal-form');
                    }

                    if (!$('#nuevoContrato-form #contrato_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'contrato_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevoContrato-form');
                    }
                    if (!$('#nuevaPoliza-form #poliza_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'poliza_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevaPoliza-form');
                    }

                    if (!$('#nuevaCredencial-form #credential_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'credential_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevaCredencial-form');
                    }

                    if (!$('#nuevaCredencialOndanet-form #credentialOndanet_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'credentialOndanet_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevaCredencialOndanet-form');
                    }

                    if (!$('#nuevogroup-form #group_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'group_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevogroup-form');
                    }

                    if (!$('#nuevoLogistica-form #logisticas_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'logisticas_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevoLogistica-form');
                    }

                    if (!$('#asignarAplicacion-form #asignar_atm_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'atm_id',
                            id: 'asignar_atm_id',
                            value: respuesta.data.id
                        }).appendTo('#asignarAplicacion-form');
                    }

                    $('#labelRed').html($('#owner_id option:selected').text());
                    var urlNuevaSucursal = $('#nuevaSucursal-form').attr('action');
                    $('#nuevaSucursal-form').attr('action', urlNuevaSucursal.replace('0', $('#owner_id').val()));
                    $('#atmId').val(respuesta.data.id);
                  
                    document.getElementById("number_contract").value = respuesta.atm_code;

                    $('#aplicacionId').empty().trigger('change');
                    $('#aplicacionId').select2({
                        data: respuesta.data.applications
                    });

                    nextStep('#btnGuardarAtm');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });


        ///////  PASO 1: AREA - COMERCIAL   ////////
        ///TAB 2 - PUNTOS DE VENTAS
        //Validacion formulario  PUNTOS DE VENTAS
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$(form).find('input[name="id"]').length) {
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
                    $(form).attr('action', respuesta.url);

                    //agregando valores al step new pos
                 
                    //agregando valores al step new pos
                    if (!$('#nuevoComprobante-form').find('input[name="owner_id"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'owner_id',
                            value: $('#owner_id').val()
                        }).appendTo('#nuevoComprobante-form');
                    }
                    //agregando valores LOGISTICAS
                    if (!$('#nuevoLogistica-form').find('input[name="owner_id"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'owner_id',
                            value: $('#owner_id').val()
                        }).appendTo('#nuevoLogistica-form');

                       
                    }
                    if (!$('#asignarAplicacion-form').find('input[name="owner_id"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'owner_id',
                            value: $('#owner_id').val()
                        }).appendTo('#asignarAplicacion-form');
                     
                    }
                    // if (!$('#asignarAplicacion-form #asignar_atm_id').length) {
                    //     $('<input>').attr({
                    //         type: 'hidden',
                    //         name: 'atm_id',
                    //         id: 'asignar_atm_id',
                    //         value: respuesta.data.id
                    //     }).appendTo('#asignarAplicacion-form');
                    // }

                    if (!$('#nuevoInternetContract-form #logisticas_branch_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'branch_id',
                            id: 'logisticas_branch_id',
                            value: respuesta.data.branch_id
                        }).appendTo('#nuevoInternetContract-form');
                    }

                    if (!$('#nuevoContrato-form #contratos_branch_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'branch_id',
                            id: 'contratos_branch_id',
                            value: respuesta.data.branch_id
                        }).appendTo('#nuevoContrato-form');
                    }
                    if (!$('#nuevoLogistica-form #conection_branch_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'branch_id',
                            id: 'conection_branch_id',
                            value: respuesta.data.branch_id
                        }).appendTo('#nuevoLogistica-form');
                    }
                    // //agregando valores al step new tipo
                    // if (!$('#nuevoTipo-form #voucher_branch_id').length) {
                    //     $('<input>').attr({
                    //         type: 'hidden',
                    //         name: 'branch_id',
                    //         id: 'po_id',
                    //         value: respuesta.data.id
                    //     }).appendTo('#nuevoInternetContract-form');
                    // }
                    $('#labelPdv').html($('#nuevoPos-form input[name=description]').val());
                    var urlNuevoTipo = $('#nuevoTipo-form').attr('action');
                    //var urlNuevoComprobante = $('#nuevoComprobante-form').attr('action'); -1
                    $('#nuevoTipo-form').attr('action', urlNuevoTipo.replace('0', respuesta.data.id));
                    //$('#nuevoComprobante-form').attr('action', urlNuevoComprobante.replace('0', respuesta.data.id)); -2

                    nextStep('#btnGuardarPos');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });

        // Validaciones del modal de nueva sucursal
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
                "more_info": {
                    required: true,
                },
                "caracteristicas_id": {
                    required: true,
                },
                "executive_id": {
                    required: true,
                },
                "related_id": {
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
                "more_info": {
                    required: "Ingrese el horario de atencion",
                },
                "executive_id": {
                    required: "Seleccione el ejecutivo",
                },     
                "caracteristicas_id": {
                    required: "Agregue una caractertistica",
                },
                "related_id": {
                    required: "Agregue un encargado",
                },
                
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnGuardarSucursal').toggleClass('active');
                $(form).find('input[type="text"]').prop('readonly', true);
                $(form).find('input[type="select"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarSucursal').toggleClass('active');
                    $(form).find('input[type="text"]').val('').prop('readonly', false);
                    $(form).find('input[type="select"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
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
                    //var urlNuevoGrupo = $('#nuevogrupo-form').attr('action');
                    $('#nuevogroup-form').attr('action', urlNuevoGroup.replace('0',respuesta.data.id));

                    // if(!$('#nuevogrupo-form input[name="id"]').length){
                    //     $('#nuevogrupo-form').attr('action',urlNuevoGrupo.replace('0',respuesta.data.id));
                    // }

                    $('#modalNuevaSucursal').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                    $('#branch_id').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de nuevo departamento
        $('#nuevoDepartamento-form').validate({
            rules: {
                "descripcion": {
                    required: true,
                }
            },
            messages: {
                "descripcion": {
                    required: "Ingrese un nombre",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnDepartamento').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnDepartamento').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop(
                        'readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevoDepartamento').modal('hide');
                    var newOption = new Option(respuesta.data.descripcion, respuesta.data.id, false, true);
                    $('#departamento_id').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de caracteristicas de la sucursal
        $('#sucursalCaracteristicas-form').validate({
            rules: {
                "correo": {
                    required: true,
                    email: true,
                },
                "referencia": {
                    required: true,
                },
              
            },
            messages: {
                "correo": {
                    required: "Ingrese el correo del cliente",
                    email: "Formato incorrecto. La direccion del correo no incluye el @"
                },
                "referencia": {
                    required: "Ingrese una referencia del local",
                },
               
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnGuardarCaracteristica').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
              
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                 
                    $('#btnGuardarCaracteristica').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop(
                        'readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalCaracteristicas').modal('hide');
                    var newOption = new Option(respuesta.data.descripcion, respuesta.data.id, false, true);
                    $('#caracteristicas_id').append(newOption).trigger('change');
                });
            }
        });



        // Validaciones del modal de nueva ciudad
        $('#nuevaCiudad-form').validate({
            rules: {
                "descripcion": {
                    required: true,
                },
                "departamento_id": {
                    required: true,
                },
            },
            messages: {
                "descripcion": {
                    required: "Ingrese la descripción",
                },
                "departamento_id": {
                    required: "Seleccione el departamento",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnCiudad').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnCiudad').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevaCiudad').modal('hide');
                    var newOption = new Option(respuesta.data.descripcion, respuesta.data.id, false, true);
                    $('#ciudad_id').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de nuevo barrio
        $('#nuevoBarrio-form').validate({
            rules: {
                "descripcion": {
                    required: true,
                },
                "ciudad_id": {
                    required: true,
                },
            },
            messages: {
                "descripcion": {
                    required: "Ingrese la descripción",
                },
                "ciudad_id": {
                    required: "Seleccione la ciudad",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnBarrio').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
                $(form).find('input[type="select"]').prop('readonly', true);

                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnBarrio').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop('readonly', false);
                    $(form).find('input[type="select"]').val('').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevoBarrio').modal('hide');
                    var newOption = new Option(respuesta.data.descripcion, respuesta.data.id, false, true);
                    $('#barrio_id').append(newOption).trigger('change');
                });
            }

        });

        // Validaciones del modal de nueva zona
        $('#nuevaZona-form').validate({
            rules: {
                "descripcion": {
                    required: true,
                },
                "user_id": {
                    required: true,
                },
            },
            messages: {
                "descripcion": {
                    required: "Ingrese la descripción",
                },
                "user_id": {
                    required: "Seleccione el ejecutivo",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnGuardarZona').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarZona').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevaZona').modal('hide');
                    var newOption = new Option(respuesta.data.descripcion, respuesta.data.id, false, true);
                    $('#zona_id_asociar').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de sociar zona a ciudad
        $('#asociarZonaCiudad-form').validate({
            rules: {
                "zona_id": {
                    required: true,
                },
                "ciudad_id": {
                    required: true,
                },
            },
            messages: {
                "zona_id": {
                    required: "Seleccione la zona",
                },
                "ciudad_id": {
                    required: "Seleccione la ciudad",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnAsociarZonaCiudad').toggleClass('active');
                $(form).find('input[descripcion="descripcion"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnAsociarZonaCiudad').toggleClass('active');
                    $(form).find('input[descripcion="descripcion"]').val('').prop('readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        $('#modalAsociarZonaCiudad').modal('hide');

                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });

                    }else if(respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        $('#modalAsociarZonaCiudad').modal('hide');

                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });

                    }

                });
            }
        });

        //al seleccionar departamento, retorna ciudades asociadas
        $(document).on('select2:select', '#departamento_id', function() {
            $.get("{{ route('atmnew.ciudades') }}", {
                departamento_id: $(this).val()
            }).done(function(response) {
                $('#ciudad_id').html(response).trigger('change');
            });
        })

        //al seleccionar ciudad, retorna barrios asociados
        $(document).on('select2:select', '#ciudad_id', function() {
            $.get("{{ route('atmnew.barrios') }}", {
                ciudad_id: $(this).val()
            }).done(function(response) {
                $('#barrio_id').html(response).trigger('change');
            });
        })

        //al seleccionar ciudad - zonas asociandas    
        $(document).on('select2:select', '#ciudad_id', function() {
            $.get("{{ route('atmnew.zonas') }}", {
                ciudad_id: $(this).val()
            }).done(function(response) {
                $('#zona_id').html(response).trigger('change');
            });
        })

        $("#nuevoBarrio").click(function() {
            $.get("{{ route('ciudades.getCiudadesAll') }}", function(data) {
                for (var i in data) {
                    var id = data[i].id;
                    var description = data[i].description;

                    $('#ciudad_id_aux').append($('<option>', {
                        value: id,
                        text: description
                    }));
                }
            });
            $("#modalNuevoBarrio").modal('show');
        });

        // $("#nuevaZona").click(function() {
        //     $.get("{{ route('ciudades.getCiudadesAll') }}", function(data) {
        //         for (var i in data) {
        //             var id = data[i].id;
        //             var description = data[i].description;

        //             $('#ciudad_id_zona').append($('<option>', {
        //                 value: id,
        //                 text: description
        //             }));
        //         }
        //     });
        //     $("#modalNuevaZona").modal('show');
        // });


        $("#asociarZonaCiudad").click(function() {
            $.get("{{ route('ciudades.getCiudadesAll') }}", function(data) {
                for (var i in data) {
                    var id = data[i].id;
                    var description = data[i].description;

                    $('#ciudad_id_asociar').append($('<option>', {
                        value: id,
                        text: description
                    }));
                }
            });

            $.get("{{ route('zonas.getZonasAll') }}", function(data) {
                for (var i in data) {
                    var id = data[i].id;
                    var description = data[i].description;

                    $('#zona_id_asociar').append($('<option>', {
                        value: id,
                        text: description
                    }));

                }
            });

            

    
            $("#modalAsociarZonaCiudad").modal('show');
        });


        ///////  PASO 2: AREAS DE LEGALES   ////////
        ///TAB 3 - CONTRATOS
        //Validaciones del formulario contrato
        $('#nuevoContrato-form').validate({
            rules: {
                "number": {
                    required: true,
                },
                "group_id": {
                    required: true,
                },
                "date_init": {
                    required: true,
                },
                "date_end": {
                    required: true,
                },
                // "observation": {
                //     required: true,
                // },
                "credit_limit": {
                    required: true,
                },
            },
            messages: {
                "group_id": {
                    required: "Seleccione el grupo",
                },
                "number": {
                    required: "Ingrese el numero del contrato",
                },
                "date_init": {
                    required: "Ingrese la fecha de inicio",
                },
                "date_end": {
                    required: "Ingrese la fecha de finalización",
                },
                // "observation": {
                //     required: "Ingrese una observación",
                // },
                "credit_limit": {
                    required: "Ingrese un limite de crédito",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$('#nuevoContrato-form #id').length) {
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
                    $(form).attr('action', respuesta.url);

                    if (!$('#nuevaPoliza-form #poliza_contrato_id').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'contrato_id',
                            id: 'poliza_contrato_id',
                            value: respuesta.data.id
                        }).appendTo('#nuevaPoliza-form');
                    }

                    nextStep('#btnGuardarContrato');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });

        // Validaciones del modal de nuevo tipo de contrato
        $('#nuevoContractType-form').validate({
            rules: {
                "description": {
                    required: true,
                }
            },
            messages: {
                "description": {
                    required: "Ingrese un nombre",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnGuardarTipoContrato').toggleClass('active');
                $(form).find('input[name="description"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarTipoContrato').toggleClass('active');
                    $(form).find('input[name="description"]').val('').prop('readonly',
                        false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    $('#modalNuevoTipoContrato').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                    $('#contract_type').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del MODAL de nuevo GRUPO
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
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnGrupo').toggleClass('active');
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGrupo').toggleClass('active');
                    $(form).find('input[name="name"]').val('').prop('readonly', false);

                    $(form).find('input[name="atm_id"]').val($('#nuevoAtm-form #id').val());

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
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
                    $('#group_id').append(newOption).trigger('change');
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$(form).find('input[name="id"]').length) {
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
                    $(form).attr('action', respuesta.url);
                });
            }
        });

        $('#grupo').hide();

        $('#group_id').on('select2:select', function() {
            var url = $('#nuevogrupo-form').attr('action');
            $('#nuevogrupo-form').attr('action', url.replace('0', $('#group_id').val()));
            var access_token = "{{ csrf_token() }}";
            var data = {
                _token: access_token,
                group_id: $('#group_id').val(),
                atm_id: $('#nuevoAtm-form #id').val()
            };
            $.ajax({
                type: 'PUT',
                url: url,
                data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
                success: function(result) {
                    console.log(result)
                }
            });

        });

        $('#managers').change(function() {
            if (this.value == 0) {
                $("#block_form").css("display", "block");
            } else {
                $("#block_form").css("display", "none");
                console.log('estas seleccionando la gestion por eglobalt');

                swal({
                    title: 'Atención!!',
                    text: "Estas creando un cliente sin reglas de bloqueo y con gestión de recaudo de Eglobalt, esta seguro de querer realizar esta acción?",
                    type: 'warning',
                    // showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'OK',
                    // cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                })
            }

        });


        ///////  PASO 2: AREAS DE LEGALES   ////////
        ///TAB 4 - POLIZAS
        // Validaciones del modal de nuevo tipo de póliza
        $('#nuevoPolicyType-form').validate({
            rules: {
                "description": {
                    required: true,
                }
            },
            messages: {
                "description": {
                    required: "Ingrese un nombre",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {

                $('#btnGuardarTipoPoliza').toggleClass('active');
                $(form).find('input[name="description"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarTipoPoliza').toggleClass('active');
                    $(form).find('input[name="description"]').val('').prop('readonly',
                        false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    $('#modalNuevoTipoPoliza').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                    $('#insurance_policy_type_id').append(newOption).trigger('change');
                });
            }
        });

        //Validaciones del formulario polizas
        $('#nuevaPoliza-form').validate({
            rules: {
                "insurance_code": {
                    required: true,
                },
                "insurance_policy_type_id": {
                    required: true,
                },
                "number": {
                    required: true,
                },
                "capital": {
                    required: true,
                },
                "status": {
                    required: true,
                },
            },
            messages: {
                "insurance_code": {
                    required: "Ingrese el código de la póliza",
                },
                "insurance_policy_type_id": {
                    required: "Seleccione el tipo de póliza",
                },
                "number": {
                    required: "Ingrese el número de contrato",
                },
                "capital": {
                    required: "Ingrese el capital",
                },
                "status": {
                    required: "Seleccione el estado",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $(form).find('input[type="select"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);
                    $(form).find('input[type="select"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$('#nuevaPoliza-form #id').length) {
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

                    $(form).attr('action', respuesta.url);

                    nextStep('#btnGuardarPoliza');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });


        /////////PASO 3: AREA DE SISTEMAS - ANTELL
        //Validaciones del formulario polizas
        $('#nuevaCredencialOndanet-form').validate({
            rules: {
                "vendedor": {
                    required: true,
                },
                "vendedor_cash": {
                    required: true,
                },
                "caja": {
                    required: true,
                },
                "sucursal": {
                    required: true,
                }
            },
            messages: {
                "vendedor": {
                    required: "Ingrese el vendedor",
                },
                "vendedor_cash": {
                    required: "Ingrese el vendedor cash",
                },
                "caja": {
                    required: "Ingrese la caja de ondanet",
                },
                "sucursal": {
                    required: "Ingrese la sucursal de ondanet",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $(form).find('input[type="select"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);
                    $(form).find('input[type="select"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$('#nuevaCredencialOndanet-form #id').length) {
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

                    $(form).attr('action', respuesta.url);

                    nextStep('#btnGuardarCredencialOndanet');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });

        /////////PASO 4: AREA DE FRAUDE - ANTELL
        //Validaciones del formulario polizas
        $('#nuevaCredencial-form').validate({
            rules: {
                "user": {
                    required: true,
                }
            },
            messages: {
                "user": {
                    required: "Ingrese el usuario",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $(form).find('input[type="select"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);
                    $(form).find('input[type="select"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$('#nuevaCredencial-form #id').length) {
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

                    $(form).attr('action', respuesta.url);

                    nextStep('#btnGuardarCredencial');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                });
            }
        });


        ///////  PASO 5: AREAS DE CONTABILIDAD   ////////
        ///TAB 6 - NUEVO COMPROBANTE PDV
        // Validaciones del modal de nueva tipo de comprobante
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#load').toggleClass('active');
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#load').toggleClass('active');
                    $(form).find('input[name="name"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
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

        // Validaciones dee nuevo comprobante pdv 
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$(form).find('input[name="id"]').length) {
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
                    $(form).attr('action', respuesta.url);
                    nextStep('#btnGuardarComprobante');
                });
            }
        });



        ///////  PASO 6: AREAS DE LOGISTICAS   ////////
        // Validaciones del modal de nuevo tipo de isp
        $('#nuevoIsp-form').validate({
            rules: {
                "description": {
                    required: true,
                }
            },
            messages: {
                "description": {
                    required: "Ingrese una descripción",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {

                $('#btnGuardarIsp').toggleClass('active');
                $(form).find('input[name="description"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarIsp').toggleClass('active');
                    $(form).find('input[name="description"]').val('').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    $('#modalNuevoIsp').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                    $('#isp_id').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de nuevo contrato se servicio de internet
        $('#nuevoInternetContract-form').validate({
            rules: {
                "isp_id": {
                    required: true,
                },
                "contract_cod": {
                    required: true,
                },
                "date_init": {
                    required: true,
                },
                "date_init": {
                    required: true,
                },
                "isp_acount_number": {
                    required: true,
                },
                "status": {
                    required: true,
                },
            },
            messages: {
                "isp_id": {
                    required: "Ingrese el contrato de isp",
                },
                "contract_cod": {
                    required: "Ingrese el codigo del contrato",
                },
                "date_init": {
                    required: "Ingrese una fecha de inicio",
                },
                "date_init": {
                    required: "Ingrese una fecha de finalizacion de contrato.",
                },
                "isp_acount_number": {
                    required: "Ingrese un numero de cuenta",
                },
                "status": {
                    required: "Seleccione un estado",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {

                $('#btnGuardarInternetServiceContract').toggleClass('active');
                $(form).find('input[name="description"]').prop('readonly', true);

                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarInternetServiceContract').toggleClass('active');
                    $(form).find('input[name="description"]').val('').prop('readonly',
                        false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    $('#modalNuevoInternetServiceContract').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data
                        .id, false, true);
                    $('#internet_service_contract_id').append(newOption).trigger('change');
                });
            }
        });

        // Validaciones del modal de nuevo tecnologia de red
        $('#nuevoNetworkTechnology-form').validate({
            rules: {
                "description": {
                    required: true,
                }
            },
            messages: {
                "description": {
                    required: "Ingrese un nombre",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {

                $('#btnGuardarNetworkTechnology').toggleClass('active');
                $(form).find('input[name="description"]').prop('readonly', true);
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnGuardarNetworkTechnology').toggleClass('active');
                    $(form).find('input[name="description"]').val('').prop('readonly',
                        false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    $('#modalNuevoNetworkTechnology').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data
                        .id, false, true);
                    $('#network_technology_id').append(newOption).trigger('change');
                });
            }
        });
            
        //Validaciones formulario NETWORK CONNECTION
        $('#nuevoLogistica-form').validate({
            rules: {
                "internet_service_contract_id": {
                    required: true,
                },
                "network_technology_id": {
                    required: true,
                },
                "housing_id": {
                    required: true,
                },
                "installation_date": {
                    required: true,
                },
                "bandwidth": {
                    required: true,
                    number: true,
                },
                "remote_access": {
                    required: true,
                },
            },
            messages: {
                "internet_service_contract_id": {
                    required: " Seleccione el contrato de servicio de internet.",
                },

                "network_technology_id": {
                    required: " Ingrese una tecnología de red.",
                },
                "housing_id": {
                    required: " Seleccione un serial.",
                },
                "installation_date": {
                    required: " Ingrese la fecha de instalación",
                },
                "bandwidth": {
                    required: " Ingrese un ancho de banda.",
                    number: "Ingrese solo valores numéricos",
                },
                "remote_access": {
                    required: "Ingrese el acceso remoto del equipo.",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)

                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();
                    $(form).find('input[type="text"]').prop('readonly', false);

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                    if (!$('#nuevoLogistica-form #id').length) {
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
                    $(form).attr('action', respuesta.url);



                    nextStep('#btnGuardarLogistica');

                    if (respuesta.tipo == 'success') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                });
            }
        });



        ///////  PASO 7: AREAS DE SISTEMAS EGLOBALT  ////////
                // CREACION DE USUARIOS          
        $('#nuevoUsuario-form').validate({
            rules: {
                "description": {
                    required: true,
                },
                "username": {
                    required: true,
                },
                "email": {
                    required: true,
                }
            },
            messages: {
                "description": {
                    required: "Ingrese un nombre",
                },
                "username": {
                    required: "Ingrese un usuario",
                },
                "email": {
                    required: "Ingrese un email",
                }
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {

                $('#btnGuardarTipoUsuario').toggleClass('active');
                $(form).find('input[name="user_id"]').prop('readonly',true);
                $.post(form.action, $(form).serialize()).done(function(respuesta){
                
                    $('#btnGuardarTipoUsuario').toggleClass('active');
                    $(form).find('input[name="user_id"]').val('').prop('readonly',false);
                    
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

                    $('#modalNuevoUsuario').modal('hide');
                    var newOption = new Option(respuesta.data.user.username, respuesta.data.id, false, true);
                    $('#user_id').append(newOption).trigger('change');
                });
            }
        });
      
        // Validaciones de nuevo ASIGNACION DE APLICACIONES
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
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('.overlay').show();
                $(form).find('input[type="text"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('.overlay').hide();

                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }

                        // MODAL RESUMEN
                    $('#reasignar').val(respuesta.reasignar);

                    $('#atmName').text(respuesta.resumen.name);
                    $('#atmCode').text(respuesta.resumen.code);
                    $('#atmOwner').text(respuesta.resumen.owner_name);
                    $('#posName').text(respuesta.resumen.pos_name);
                    $('#posBranch').text(respuesta.resumen.branch_name);
                    $('#posOndanetCode').text(respuesta.resumen.ondanet_code);
                    $('#posCode').text(respuesta.resumen.pos_code);

                    $('#contractNumber').text(respuesta.resumen.contrato_numero);
                   // $('#contractDateInit').text(respuesta.resumen.contrato_vigencia_ini);
                    $('#contractDateEnd').text(respuesta.resumen.contrato_vigencia_end);
                    $('#contractLimitCredit').text(respuesta.resumen.contrato_limite_credito);

                    $('#insuranceType').text(respuesta.resumen.poliza_tipo);
                    $('#insuranceCode').text(respuesta.resumen.poliza_codigo);
                    $('#insuranceNumber').text(respuesta.resumen.poliza_numero);
                    $('#insuranceCapital').text(respuesta.resumen.poliza_capital);

                    $('#networkNroContrato').text(respuesta.resumen.network_contrato);
                    $('#networkTech').text(respuesta.resumen.network_tecnologia);
                    $('#networkBandwidth').text(respuesta.resumen.network_anchobanda);
                    $('#networkFechaInstalacion').text(respuesta.resumen.network_fecha_instalacion);

                    $('#contractInternetDescription').text(respuesta.resumen.contract_internet_descripcion);
                    $('#contractInternetAcount').text(respuesta.resumen.contract_internet_contrato);
                    $('#contractInternetDateInit').text(respuesta.resumen.contract_internet_fecha_inicio);
                    $('#contractInternetDateEnd').text(respuesta.resumen.contract_internet_fecha_fin);

                    $('#servicio_tigo').text(respuesta.credencial_tigo.servicio);
                    $('#servicio_tigo_money').text(respuesta.credencial_tigo_money.servicio);

                    // $('#contractNumber').text(respuesta.resumen.pos_code);

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#tipoComprobante').text(respuesta.resumen.description);
                    }

                    if (typeof respuesta.resumen.grupo != 'undefined') {
                        $('#grupo').text(respuesta.resumen.grupo);
                    }

                    if (typeof respuesta.resumen.ruc != 'undefined') {
                        $('#ruc').text(respuesta.resumen.ruc);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#timbrado').text(respuesta.resumen.stamping);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#numeracionDesde').text(respuesta.resumen.from_number);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#numeracionHasta').text(respuesta.resumen.to_number);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#validoDesde').text(respuesta.resumen.valid_from);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#validoHasta').text(respuesta.resumen.valid_until);
                    }

                    if(typeof respuesta.resumen.description != 'undefined'){
                        $('#puntoExp').text(respuesta.resumen.expedition_point);
                    }

                    if (typeof respuesta.resumen.description != 'undefined') {
                        $('#contractDateInit').text(respuesta.resumen.contrato_vigencia_ini);
                    }


                    $('#aplicacion').text(respuesta.resumen.application_name);
                    $('#modalResumen').modal('show');

                    $(document).on('click', '#bntConfirmarResumen', function() {
                        $('#modalResumen').modal('hide');
                        $(form).find('input[type="text"]').prop('readonly', false);
                        swal({
                            title: respuesta.titulo,
                            text: respuesta.mensaje,
                            html: respuesta.mensaje,
                            type: respuesta.tipo,
                            closeOnClickOutside: false
                        }, function() {
                            window.location = '{{ url('atmnew') }}?id=' + $(
                                '#nuevoAtm-form input[name=id]').val();
                        });
                    })
                });
            }
        });

        $('#aplicacionId').on('select2:select', function() {
            var url = $('#asignarAplicacion-form').attr('action');
            url = url.replace(/\d+/g, '0');
            $('#asignarAplicacion-form').attr('action', url.replace('0', $('#aplicacionId').val()));
        });



        //BTN OMITIR
        $(document).on('click', '#btnOmitirPoliza', function(e) {
            e.preventDefault();
            nextStep('#btnGuardarPoliza');
        });
        $(document).on('click', '#btnOmitirPdv', function(e) {
            e.preventDefault();
            nextStep('#btnGuardarComprobante');
        });

        
        // Funcion que genera el formulario por pasos
        var navListItems = $('div.setup-panel div button'),
            allWells = $('.setup-content'),
            allNextBtn = $('.nextBtn');
        formSteps();

        function formSteps() {
            allWells.hide();

            navListItems.click(function(e) {
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

            if (btnId == '#btnGuardarAtm') {
                $('#aplicacionId').trigger('select2:select');
            }

            if (btnId == '#btnGuardarPos') {
                $('.nav-tabs a[href="#tab_3"]').tab('show');
            }

            // if(btnId == '#btnGuardarContrato'){
            //     $('.nav-tabs a[href="#tab_4"]').tab('show');
            // }

            if (btnId == '#btnGuardarPoliza') {
                $('.nav-tabs a[href="#step-4"]').tab('show');
            }
            if (btnId == '#btnGuardarCredencialOndanet') {
                $('.nav-tabs a[href="#tab_6"]').tab('show');
            }
            if (btnId == '#btnGuardarCredencial') {
                $('.nav-tabs a[href="#tab_7"]').tab('show');
            }
            if (btnId == '#btnGuardarLogistica') {
                $('.nav-tabs a[href="#step-7"]').tab('show');
            }
        };

        $(document).on('click', '.atras', function() {
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

        $(window).on("beforeunload", function() {
            return "";
        });


        $('.btnNext').click(function() {
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        });

        $('.btnPrevious').click(function() {
            $('.nav-tabs > .active').prev('li').find('a').trigger('click');
        });


        /* On generate key button click*/
        $(document).on('click', '.btn-generate-key', function(e){
            e.preventDefault();
            var key_text_control = $(this).parent().find('.key');
            var id = $(this).parent().find(".key").attr("id");
            swal({
                title: "Atención!",
                text: "Está seguro que desea generar una nueva clave para este ATM?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si, generar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var form = $('#form-generate-hash');
                    var url = form.attr('action');
                    var data = form.serialize();
                    $.post(url,data, function(result){
                        $("#"+id).val(result);
                    }).fail(function (){
                        swal('No se pudo realizar la petición.');
                    });

                }
            });
        });



    //end
    });
</script>
