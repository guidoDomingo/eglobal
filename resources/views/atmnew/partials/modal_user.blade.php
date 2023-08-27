<!-- Modal -->
<div id="modalNuevoUsuario" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Creación de usuarios </h4>
            </div>
            {!! Form::open(['route' => ['users.store'], 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoUsuario-form']) !!}
            <div class="modal-body">
                <div class="box-body">
                    @include('partials._messages')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel with-nav-tabs">
                                <div class="panel-heading">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                                Datos de usuario </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="tab_help_0">
                                            @include('users.partials.fields')
                                            {!! Form::hidden('abm','v2') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary has-spinner" id="btnGuardarUsuario"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
            </div>
            {!! Form::close() !!}
        </div>

    </div>
</div>
<style type="text/css">
    /*se agranda el modal para poder cargar el map*/
    @media screen and (min-width: 1200px){
        .modal-large>.modal-dialog{
            width: 1200px;
        }
    }
</style>
{{-- modal end --}}