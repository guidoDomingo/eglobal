<!-- Modal -->
<div id="modalNuevaSucursal" class="modal modal-large fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Nueva Sucursal <label class="idTransaccion"></label></h4>
            </div>
            {!! Form::open(['route' => ['owner.branches.store',0] , 'method' => 'POST', 'role' => 'form','id' => 'nuevaSucursal-form']) !!}
            <div class="modal-body">
                <div class="box-body">
                    @include('partials._messages')
                    <div class="row">
                        <div class="col-md-6">
                            @include('branches.partials.modal_fields')
                        </div>

                        <div class="col-md-6">
                            @include('branches.partials.modal_maps')
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary has-spinner" id="btnGuardarSucursal"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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