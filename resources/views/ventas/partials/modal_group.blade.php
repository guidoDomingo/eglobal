<!-- Modal -->
<div id="modalNuevoGrupo" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Nuevo Grupo <label></label></h4>
            </div>
            {!! Form::open(['route' => 'groups.store_venta' , 'method' => 'POST', 'role' => 'form','id' => 'nuevogroup-form']) !!}
            <div class="modal-body">
                <div class="box-body">
                    @include('partials._flashes')
                    @include('partials._messages')
                    @include('groups.partials.fields')
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary has-spinner" id="btnGuardarGrupo"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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