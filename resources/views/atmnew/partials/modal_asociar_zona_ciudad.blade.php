<!-- Modal -->
<div id="modalAsociarZonaCiudad" class="modal fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align:center;">Asignar Ciudad - Zona</h4>
            </div>
            <div class="box box-primary">
                {!! Form::open(['route' => 'zonas.asociar' , 'method' => 'POST', 'role' => 'form','id' => 'asociarZonaCiudad-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('ciudad', 'Ciudad') !!}
                                    {!! Form::select('ciudad_id', [], null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción','style' => 'width: 100%', 'id' => 'ciudad_id_asociar']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('zona', 'Zona') !!}  @if (\Sentinel::getUser()->hasAccess('zonas_asociacion'))<a style="margin-left: 2em" href='#' id="nuevaZona" data-toggle="modal" data-target="#modalNuevaZona"><small>Agregar <i class="fa fa-plus"></i></small></a>@endif
                                    {!! Form::select('zona_id', [], null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción','style' => 'width: 100%', 'id' => 'zona_id_asociar']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary has-spinner" id="btnAsociarZonaCiudad"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
                </div>
                {!! Form::close() !!}
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
    .modal.fade {
    background: rgba(0,0,0,0.5);
}
</style>
{{-- modal end --}}

