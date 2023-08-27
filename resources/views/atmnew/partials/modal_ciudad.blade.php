<!-- Modal -->
<div id="modalNuevaCiudad" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Nueva Ciudad <label class="idTransaccion"></label></h4>
            </div>
            <div class="box box-primary">

                {!! Form::open(['route' => 'ciudades.store', 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaCiudad-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('descripcion', 'Descripción') !!}
                                    {!! Form::text('descripcion', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la Ciudad', 'id' => 'descripcion_ciudad']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('departamento', 'Departamento') !!}
                                    {!! Form::select('departamento_id', $departamentos, null, ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción','style' => 'width: 100%']) !!}
                                </div>
                                @if (isset($ciudad))
                                    <div class="form-group">
                                        {!! Form::label('created_by', 'Creado el:') !!}
                                        <p> {{ date('d/m/y H:i', strtotime($ciudad->created_at)) }}</p>
                                    </div>
                                @endif
                                @if (isset($ciudad))
                                    <div class="form-group">
                                        {!! Form::label('updated_by', 'Modificado el:') !!}
                                        <p>{{ date('d/m/y H:i', strtotime($ciudad->updated_at)) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary has-spinner" id="btnCiudad"><span class="spinner"><i
                                class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    /*se agranda el modal para poder cargar el map*/
    @media screen and (min-width: 1200px) {
        .modal-large>.modal-dialog {
            width: 1200px;
        }
    }

</style>
{{-- modal end --}}
{{-- Scripts --}}
@section('page_scripts')
    {{-- <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script> --}}
@endsection