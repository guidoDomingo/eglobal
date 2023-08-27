@extends('layout')

@section('title')
    BAJA | Nota de retiro
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Elaboración de documentaciones
            <small>Creación de Nota de retiro</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Nota de retiro</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'notaretiro.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoRetiro-form']) !!}
                            @include('atm_baja.notas_retiros.partials.fields')
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$group_id.'/'.$group_id.'/retiro') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>  

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

<script type="text/javascript">
    $('#listadoAtms').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "displayLength": 3,
            "language":{"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"},
            "bInfo" : false


    });
    
    $('.select2').select2();

    $('#fecha').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    $('#fecha').datepicker("setDate", new Date());

    //validacion formulario 
    $('#nuevoRetiro-form').validate({
        rules: {
            "nombre_comercial": {
                required: true,
            },
            "propietario": {
                required: true,
            },
            "referencia": {
                required: true,
            },
            "representante_legal": {
                required: true,
            },
            "ruc_representante": {
                required: true,
            },
            "direccion": {
                required: true,
            },
            "fecha": {
                required: true,
            },
            "correos": {
                required: true,
            },
        },
        messages: {
            "ruc_representante": {
                required: "Ingrese el ruc del representante.",
            },
            "propietario": {
                required: "Ingrese el nombre del propietario.",
            },
            "representante_legal": {
                required: "Ingrese el nombre del representante legal.",
            },
            "referencia": {
                required: "Ingrese el motivo o referencia.",
            },
            "nombre_comercial": {
                required: "Ingrese el nombre del comercial.",
            },
            "direccion": {
                required: "Ingrese la dirección.",
            },
            "fecha": {
                required: "Seleccione una fecha.",
            },
            "correos": {
                required: "Ingrese al menos un destinatario.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });
   
</script>
<script>
    //CORREOS MULTIPLES
    (function( $ ){
    
        $.fn.multiple_emails = function(options) {
        
        // Default options
        var defaults = {
            checkDupEmail: true,
            theme: "Bootstrap",
            position: "top"
        };
        
        // Merge send options with defaults
        var settings = $.extend( {}, defaults, options );
        
        var deleteIconHTML = "";
        if (settings.theme.toLowerCase() == "Bootstrap".toLowerCase())
        {
            deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><span class="glyphicon glyphicon-remove"></span></a>';
        }
        else if (settings.theme.toLowerCase() == "SemanticUI".toLowerCase() || settings.theme.toLowerCase() == "Semantic-UI".toLowerCase() || settings.theme.toLowerCase() == "Semantic UI".toLowerCase()) {
            deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><i class="remove icon"></i></a>';
        }
        else if (settings.theme.toLowerCase() == "Basic".toLowerCase()) {
            //Default which you should use if you don't use Bootstrap, SemanticUI, or other CSS frameworks
            deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><i class="basicdeleteicon">Remove</i></a>';
        }
        
        return this.each(function() {
            //$orig refers to the input HTML node
            var $orig = $(this);
            var $list = $('<ul class="multiple_emails-ul" />'); // create html elements - list of email addresses as unordered list

            if ($(this).val() != '' && IsJsonString($(this).val())) {
                $.each(jQuery.parseJSON($(this).val()), function( index, val ) {
                    $list.append($('<li class="multiple_emails-email"><span class="email_name" data-email="' + val.toLowerCase() + '">' + val + '</span></li>')
                    .prepend($(deleteIconHTML)
                            .click(function(e) { $(this).parent().remove(); refresh_emails(); e.preventDefault(); })
                    )
                    );
                });
            }
            
            var $input = $('<input type="text" class="multiple_emails-input text-left" />').on('keyup', function(e) { // input
                $(this).removeClass('multiple_emails-error');
                var input_length = $(this).val().length;
                
                var keynum;
                if(window.event){ // IE					
                    keynum = e.keyCode;
                }
                else if(e.which){ // Netscape/Firefox/Opera					
                    keynum = e.which;
                }
                
                //if(event.which == 8 && input_length == 0) { $list.find('li').last().remove(); } //Removes last item on backspace with no input
                
                // Supported key press is tab, enter, space or comma, there is no support for semi-colon since the keyCode differs in various browsers
                if(keynum == 9 || keynum == 32 || keynum == 188) { 
                    display_email($(this), settings.checkDupEmail);
                }
                else if (keynum == 13) {
                    display_email($(this), settings.checkDupEmail);
                    //Prevents enter key default
                    //This is to prevent the form from submitting with  the submit button
                    //when you press enter in the email textbox
                    e.preventDefault();
                }

            }).on('blur', function(event){ 
                if ($(this).val() != '') { display_email($(this), settings.checkDupEmail); }
            });

            var $container = $('<div class="multiple_emails-container" />').click(function() { $input.focus(); } ); // container div

            // insert elements into DOM
            if (settings.position.toLowerCase() === "top")
                $container.append($list).append($input).insertAfter($(this));
            else
                $container.append($input).append($list).insertBefore($(this));

            /*
            t is the text input device.
            Value of the input could be a long line of copy-pasted emails, not just a single email.
            As such, the string is tokenized, with each token validated individually.
            
            If the dupEmailCheck variable is set to true, scans for duplicate emails, and invalidates input if found.
            Otherwise allows emails to have duplicated values if false.
            */
            function display_email(t, dupEmailCheck) {
                
                //Remove space, comma and semi-colon from beginning and end of string
                //Does not remove inside the string as the email will need to be tokenized using space, comma and semi-colon
                var arr = t.val().trim().replace(/^,|,$/g , '').replace(/^;|;$/g , '');
                //Remove the double quote
                arr = arr.replace(/"/g,"");
                //Split the string into an array, with the space, comma, and semi-colon as the separator
                arr = arr.split(/[\s,;]+/);
                
                var errorEmails = new Array(); //New array to contain the errors
                
                var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                
                for	(var i = 0; i < arr.length; i++) {
                    //Check if the email is already added, only if dupEmailCheck is set to true
                    if ( dupEmailCheck === true && $orig.val().indexOf(arr[i]) != -1 ) {
                        if (arr[i] && arr[i].length > 0) {
                            new function () {
                                var existingElement = $list.find('.email_name[data-email=' + arr[i].toLowerCase().replace('.', '\\.').replace('@', '\\@') + ']');
                                existingElement.css('font-weight', 'bold');
                                setTimeout(function() { existingElement.css('font-weight', ''); }, 1500);
                            }(); // Use a IIFE function to create a new scope so existingElement won't be overriden
                        }
                    }
                    else if (pattern.test(arr[i]) == true) {
                        $list.append($('<li class="multiple_emails-email"><span class="email_name" data-email="' + arr[i].toLowerCase() + '">' + arr[i] + '</span></li>')
                            .prepend($(deleteIconHTML)
                                    .click(function(e) { $(this).parent().remove(); refresh_emails(); e.preventDefault(); })
                            )
                        );
                    }
                    else
                        errorEmails.push(arr[i]);
                }
                // If erroneous emails found, or if duplicate email found
                if(errorEmails.length > 0)
                    t.val(errorEmails.join("; ")).addClass('multiple_emails-error');
                else
                    t.val("");
                refresh_emails ();
            }
            
            function refresh_emails () {
                var emails = new Array();
                var container = $orig.siblings('.multiple_emails-container');
                container.find('.multiple_emails-email span.email_name').each(function() { emails.push($(this).html()); });
                $orig.val(JSON.stringify(emails)).trigger('change');
            }
            
            function IsJsonString(str) {
                try { JSON.parse(str); }
                catch (e) {	return false; }
                return true;
            }
            
            return $(this).hide();

        });
        
    };
    
    })(jQuery);

     //Plug-in function for the bootstrap version of the multiple email
     $(function() {
         //To render the input device to multiple email input using BootStrap icon
         $('#correos').multiple_emails({position: "bottom"});
         //OR $('#correos').multiple_emails("Bootstrap");
         
         //Shows the value of the input device, which is in JSON format
     $('#current_emailsBS').text($('#correos').val());
    //			$('#correos').change( function(){
    //				$('#current_emailsBS').text($(this).val());
         
     });

</script>
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
     <style>
        label span {
            font-size: 1rem;
        }

        label.error {
            color: red;
            font-size: 1rem;
            display: block;
            margin-top: 5px;
        }

        input.error {
            border: 1px dashed red;
            font-weight: 300;
            color: red;
        }

        .borderd-campaing {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 550px;
            margin-top: 20px;
            position: relative;
        }

        .borderd-campaing .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        /* MULTIPLE CORREOS */
        .multiple_emails-container { 
            border:1px #141414 solid; 
            border-radius: 1px; 
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075); 
            padding:0; margin: 0; cursor:text; width:100%; 
        }

        .multiple_emails-container input { 

            width:100%; 
            border:0; 
            outline: none; 
            margin-bottom:30px; 
            padding-left: 5px; 
            
        }

        ,,.multiple_emails-container input{
            border: 0 !important;
        }

        .multiple_emails-container input.multiple_emails-error {	
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px red !important; 
            outline: thin auto red !important; 
        }

        .multiple_emails-container ul {	
            list-style-type:none; 
            padding-left: 0; 
        }

        .multiple_emails-email { 
            margin: 3px 5px 3px 5px; 
            padding: 3px 5px 3px 5px; 
            border:1px #accef8 solid;	
            border-radius: 3px; 
            background: #F3F7FD; 
        }

        .multiple_emails-close { 
            float:left; 
            margin:0 3px;
        }

         /* INFO */
         .borderd-info {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 550px;
            margin-top: 20px;
            position: relative;
            /* height: auto; */
        }

        .borderd-info .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .borderd-info .campaing {
            padding: 10px;
        }
        .container-info {
            margin-top: 20px;
        }

    </style>
@endsection