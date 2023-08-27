<!-- Modal -->
<div id="modalNuevoDepartamento" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Nuevo Departamento <label class="idTransaccion"></label></h4>
            </div>
            <div class="box box-primary">
                {!! Form::open(['route' => 'departamentos.store' , 'method' => 'POST', 'role' => 'form','id' => 'nuevoDepartamento-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-md-12">
                                {{-- @include('departamentos.partials.fields') --}}
                                <div class="form-group">
                                    {!! Form::label('descripcion', 'Descripción') !!}
                                    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' ,'id' => 'descripcion_departamento']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary has-spinner" id="btnDepartamento"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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
@section('page_scripts')
    {{-- <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script> --}}
@endsection