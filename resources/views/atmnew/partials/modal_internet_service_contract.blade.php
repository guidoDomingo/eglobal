<!-- Modal -->
<div id="modalNuevoInternetServiceContract" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align:center;">Nuevo Contrato de servicio de Internet</h4>
            </div>
            {!! Form::open(['route' => ['internet.contract.store', 123], 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoInternetContract-form']) !!}
            <div class="modal-body"> 
                <div class="box-body">
                    @include('partials._messages')
                    @include('internet_service_contract.partials.modal_fields')

                    
                    @if(isset($pointofsale))
                    @if(!empty($pointofsale))
                        {!! Form::hidden('branch_id',$branch_internet_contract->id ?? "") !!}
                    @endif
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary has-spinner" id="btnGuardarInternetServiceContract"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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
