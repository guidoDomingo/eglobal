<!-- Modal -->
<div id="modalNuevoBarrio" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Nuevo Barrio <label class="idTransaccion"></label></h4>
            </div>
            <div class="box box-primary">
                {!! Form::open(['route' => 'barrios.store' , 'method' => 'POST', 'role' => 'form','id' => 'nuevoBarrio-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('descripcion', 'Descripción') !!}
                                    {!! Form::text('descripcion', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del Barrio', 'id' => 'descripcion_barrio']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('ciudad', 'Ciudad') !!}
                                    {!! Form::select('ciudad_id', [], null, ['class' => 'form-control select2','placeholder' => 'Seleccione una opción','style' => 'width: 100%', 'id' => 'ciudad_id_aux']) !!}
                                </div>
                                @if(isset($barrio))
                                    <div class="form-group">
                                        {!! Form::label('created_by', 'Creado el:') !!}
                                        <p> {{ date('d/m/y H:i', strtotime($barrio->created_at)) }}</p>
                                    </div>
                                @endif
                                @if(isset($barrio))
                                    <div class="form-group">
                                        {!! Form::label('updated_by', 'Modificado el:') !!}
                                        <p>{{ date('d/m/y H:i', strtotime($barrio->updated_at)) }}</p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary has-spinner" id="btnBarrio"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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
    
</style>


{{-- modal end --}}
{{-- Scripts --}}
{{-- @section('js')
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
@endsection --}}
