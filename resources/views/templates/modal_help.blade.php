<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body" title='Opciones'>
                <button type="button" class="btn btn-default" title="Ayuda e información" data-toggle="modal"
                    data-target="#modal_help" style="border-radius: 5px; margin-botton: 5px">
                    <span class="fa fa-question" aria-hidden="true"></span> Ayuda
                </button>

                &nbsp;
                &nbsp;

                <button type="button" class="btn btn-default" title="Contraer divisiones."
                    style="border-radius: 5px; margin-botton: 5px" onclick="contract_expand()" id="contract_expand">
                    <i class="fa fa-minus"></i> Contraer
                </button>

                &nbsp;
                &nbsp;

                <button type="button" class="btn btn-default" title="Imprimir página"
                    style="border-radius: 5px; margin-botton: 5px" onclick="print()" id="print">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ayuda-->
<div id="modal_help" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
        style="width: 800px; background: white; border-radius: 5px">
        <!-- Modal content-->
        <div class="modal-content" style="border-radius: 10px">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="modal-title" style="font-size: 20px;">
                    Ayuda e información &nbsp; <small> <b> </b> </small>
                </div>
            </div>
        </div>

        <div class="modal-body">
            <!-- TAB PANNEL -->
            <div class="panel with-nav-tabs">
                <div class="panel-heading">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                Resumen por totales </a>
                        </li>
                        <li><a href="#tab_help_1" data-toggle="tab">
                                Búsqueda personalizada </a>
                        </li>
                        <li><a href="#tab_help_2" data-toggle="tab">
                                Transacciones</a></li>
                        <li><a href="#tab_help_3" data-toggle="tab">
                                Formulario</a></li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <!-- Solamente el contenido se cambia -->
                        <div class="tab-pane fade in active" id="tab_help_0">
                            <div class="callout callout-info">
                                <h5><b>Definición de Resumen por totales:</b></h5>
                                <p>El resumen por totales muestra un resumen de la cantidad de transacciones por
                                    estado, el total en guaranies y el porcentaje que representa en el periodo de
                                    tiempo. </p>
                            </div>

                            <div class="callout callout-default">
                                <b>Transacción pendiente:</b> Son transacciones que aún no han sido procesadas
                                por el
                                serivicio ussd. <br />

                                <b>Transacción exitosa:</b>
                                Son transacciones que ya han sido procesadas por el serivicio ussd y se envió el
                                saldo
                                al cliente. <br />

                                <b>Transacción fallida:</b>
                                Son transacciones que ya han sido procesadas por el serivicio ussd y la
                                operadora
                                respondió con un mensaje de error.
                                <br />

                                <b>Transacción desconocida:</b>
                                Son transacciones que ya han sido procesadas por el serivicio ussd y no pudo
                                detectar el
                                origen del error. <br />
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_help_1">
                            <div class="callout callout-info">
                                <h5><b>Definición de búsqueda personalizada:</b> </h5>
                                Los filtros de búsqueda personalizada
                                sirven para traer registros
                                guardados según los filtros elegidos.
                            </div>

                            <div class="callout callout-default">
                                <b>Teléfono:</b> Sirve para filtrar la búsqueda ingresando el número de teléfono
                                del cliente. <br />

                                <b>Fecha:</b>
                                Sirve para filtrar la búsqueda ingresando una fecha especifica o un rango de
                                fecha personalizado. <br />

                                <b>Estado:</b>
                                Sirve para filtrar la búsqueda por el estado de la transacción. Los estados son:
                                <br />

                                <ul>
                                    <li><b> Pendiente: </b> La transacción todavía no se ejecutó por el servicio
                                        ussd.</li>
                                    <li><b> Exitosa: </b> La transacción ya se ejecutó por el servicio ussd y no
                                        ocurrió errores.</li>
                                    <li><b> Fallida: </b> La transacción se ejecutó por el servicio ussd y
                                        ocurrió un error.</li>
                                    <li><b> Desconocido: </b> La transacción se ejecutó por el servicio ussd y
                                        ocurrió
                                        un error pero no se pudo detectar el problema. </li>
                                </ul>

                                <b>Límite:</b>
                                Sirve para filtrar la búsqueda ingresando el número de teléfono del cliente. <br />

                                <b>Buscar:</b>
                                Haciendo click en el botón buscar ejecuta la acción para traer los registros
                                según los filtros ingresados por el usuario.
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_help_2">
                            <div class="callout callout-info">
                                <h5><b>Definición de transacciones:</b> </h5>
                                En la sección de transacciones se muestra una lista de todas las transacciones
                                existentes según los filtros de busqueda previamente seleccionados.

                            </div>

                            <div class="callout callout-default">
                                <h5><b>Columnas de la tabla:</b></h5>

                                <b>Estado:</b> Muestra en que estado se encuentra la transacción, la tabla tiene los
                                registros agrupados por estado. <br />

                                <b>Servicio:</b>
                                Es el paquete que contiene las opciones del menú por ejemplo: Paquete de Internet o
                                llamadas <br />

                                <b>Opción:</b>
                                Es la opción de los paquetes que el cliente seleccionó desde el cajero.
                                <br />

                                <b>Monto:</b>
                                Es el precio que tiene la opción seleccionada por el cliente. <br />

                                <b>Teléfono:</b>
                                Es el teléfono que el cliente seleccionó para cargar el paquete. <br />

                                <b>Opciones:</b>
                                En la columna de opciones hay 2 tipo de botones posibles:

                                <ul>
                                    <li><b> <i class="fa fa-eye"></i> Vista: </b> El botón de vista solo permite ver
                                        más
                                        datos sobre la transacción.</li>

                                    <li><b> <i class="fa fa-pencil"></i> Edición: </b> El botón de edición permite
                                        ver
                                        más
                                        datos sobre la transacción y también editar el registro.</li>
                                </ul>
                            </div>

                            <div class="callout callout-default">
                                <h5><b>Observación:</b></h5>
                                <ul>
                                    <li> Las transacciones con estado: <b>Pendiente y Exitosa</b> solo pueden
                                        <b>visualizarse y no
                                            editarse</b>.
                                    </li>

                                    <li> Solo las transacciones con estado: <b>Fallido o Desconocido</b> son
                                        posibles
                                        <b>visualizar y editar</b>.
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_help_3">
                            <div class="callout callout-info">
                                <h5><b>Definición de formulario:</b> </h5>
                                En el formulario podemos ver los datos del registro o editarlo.
                            </div>


                            <div class="panel with-nav-tabs">
                                <div class="panel-heading">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#tab_form_1" data-toggle="tab">
                                                Campos Visibles </a>
                                        </li>
                                        <li><a href="#tab_form_2" data-toggle="tab">
                                                Campos Editables</a></li>

                                        <li><a href="#tab_form_3" data-toggle="tab">
                                                Botones</a></li>
                                    </ul>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="tab_form_1">
                                            <div class="callout callout-default">
                                                <h5><b>Definición de Campos Visibles:</b></h5>
                                                Son los campos que están solo para visualización y no pueden
                                                editarse.
                                            </div>

                                            <div class="callout callout-default">
                                                <b>Número de carga:</b> Es el número de la carga asignado a el
                                                registro.
                                                <br />

                                                <b>Número de transacción:</b> Es el número de transacción asignado a
                                                el
                                                registro. <br />

                                                <b>Mensaje de operadora:</b>
                                                Es el mensaje obtenido de la operadora.
                                                <br />

                                                <b>Entrada:</b>
                                                Es la fecha y hora que la solicitud de carga entró al sistema.
                                                <br />

                                                <b>Ejecución:</b>
                                                Es la fecha y hora en cual el servicio ussd ejecutó la solicitud.
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="tab_form_2">

                                            <div class="callout callout-default">
                                                <h5><b>Definición de Campos Editables:</b> </h5>
                                                Son los campos que están solo para ser editados por el usuario.
                                            </div>

                                            <div class="callout callout-default">
                                                <b>Número de Recarga:</b> Es el número de la carga identificable del
                                                registro. <br />

                                                <b>Nuevo número de teléfono:</b> Es el nuevo número comunicado por
                                                el
                                                cliente. <br />

                                                <b>Razón:</b>
                                                Es la razón por la cual la carga no pudo ser realizada.
                                                <br />

                                                <b>Comentario:</b>
                                                En este campo se puede ingresar una descripción más amplia sobre la
                                                situación ocurrida. <br />
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="tab_form_3">
                                            <div class="callout callout-default">
                                                <h5><b>Definición de Botones:</b></h5>
                                                En el formulario de vista o edición se puede encontrar dos
                                                botones
                                                que son: Cancelar y Confirmar.
                                            </div>

                                            <div class="callout callout-default">
                                                <ul>
                                                    <li><b> <i class="fa fa-remove"></i> Cancelar: </b> Este botón
                                                        sirve
                                                        para cancelar la operación y cerrar la ventana del
                                                        formulario.
                                                    </li>

                                                    <li><b> <i class="fa fa-save"></i> Confirmar: </b> Este botón
                                                        sirve
                                                        para confirmar la transacción editada.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- FIN TAB PANNEL -->
            </div>

            <div class="modal-footer">
                <div style="float:right">
                    <div class="btn-group mr-2" role="group">
                        <button class="btn btn-danger pull-right" title="Cerrar ayuda e información."
                            style="margin-right: 10px" data-dismiss="modal">
                            <span class="fa fa-remove" aria-hidden="true"></span>
                            &nbsp; Cerrar ayuda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
