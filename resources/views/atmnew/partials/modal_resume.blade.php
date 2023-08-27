<!-- Modal -->
<div id="modalResumen" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Resumen </h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    @include('partials._messages')
                    @include('atm.partials.resume_fields')
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary has-spinner" id="bntConfirmarResumen"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Confirmar</button>
            </div>
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