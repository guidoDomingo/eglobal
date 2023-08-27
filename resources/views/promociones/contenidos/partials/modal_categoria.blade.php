<!-- Modal -->
<div id="modalNuevaCategoria" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <strong><h4 class="modal-title" style="text-align:center;">Nueva Categoría</h4></strong>
            </div>

            <div class="box box-primary">
                {!! Form::open(['route' => 'promotions_categories.store', 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaCategoria-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                        @include('partials._messages')
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <div class="form-group">                                
                                            {!! Form::label('name', 'Nombre:') !!}
                                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ingrese un nombre para la categoría', 'id' => 'name']) !!}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <div class="bootstrap-timepicker">
                                        <div class="form-group">
                                            <label>Desde:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control timepicker" name="start_time"> 
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <div class="bootstrap-timepicker">
                                        <div class="form-group">
                                            <label>Hasta:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control timepicker" name="end_time">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
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
                    <button type="submit" class="btn btn-primary has-spinner" id="btnCategoria"><span class="spinner"><i class="fa fa-circle-o-notch fa-spin"></i></span> Guardar</button>
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
