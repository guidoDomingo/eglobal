<!-- Modal -->
<div id="modalNuevaAsociacion" class="modal fade modal-xl" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title"><i class="fa fa-map" aria-hidden="true"></i>&nbsp; Alcance de la campaña </h3>
            </div>
            <div class="box box-primary">
                {{-- <div class="row">
                    <div class="col-md-12">
                        <div class="box box-default collapsed-box">
                            <div class="box-header with-border">
                                <h3 class="box-title" style="text-align:center">Ver mapa</h3>

                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                               
                                <div class="row">
        
                                    <div class="col-md-2">
                                        <label for="record_limit">Cantidad de terminales:</label>
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="record_limit" id="record_limit"
                                                min="0" max="10000" value="1000"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2">
                                        <label for="radius">Radio de alcance:</label>
                                        <div class="form-group">
                                            <input type="number" class="form-control" name="radius" id="radius" min="0"
                                                max="10000" value="1000"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4">
                                        <label for="atm_id">Terminal:</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="atm_id" id="atm_id"
                                                placeholder="Todos"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4">
                                        <label for="departament_id">Departamento:</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="departament_id" id="departament_id"
                                                placeholder="Todos"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4" style="display: none">
                                        <label for="city_id">Ciudad:</label>
                                        <div class="form-group">
                                            <!--<input type="text" class="form-control" name="city_id" id="city_id"
                                                placeholder="Todos"></input>-->
        
                                                <select name="city_id" id="city_id" class="select2"
                                                style="width: 100%"></select>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4" style="display: none">
                                        <label for="district_id">Barrio:</label>
                                        <div class="form-group">
                                            <!--<input type="text" class="form-control" name="district_id" id="district_id"
                                                    placeholder="Todos"></input>-->
        
                                            <select name="district_id" id="district_id" class="select2"
                                                style="width: 100%"></select>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4" style="display: none">
                                        <label for="promotions_providers_id">Proveedor:</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="promotions_providers_id"
                                                id="promotions_providers_id" placeholder="Todos"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4" style="display: none">
                                        <label for="business_id">Empresa:</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="business_id" id="business_id"
                                                placeholder="Todos"></input>
                                        </div>
                                    </div>
        
                                    <div class="col-md-4" style="display: none">
                                        <label for="promotions_branch_id">Sucursal:</label>
                                        <div class="form-group">
                                            <!--<input type="text" class="form-control" name="promotions_branch_id"
                                                            id="promotions_branch_id" placeholder="Todos"></input>-->
        
                                            <select name="promotions_branch_id" id="promotions_branch_id" class="select2"
                                                style="width: 100%"></select>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2">
                                        <label for="radius_view">Radios de alcance:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="radius_view" name="radius_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2">
                                        <label for="departament_view">Departamentos:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="departament_view" name="departament_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2">
                                        <label for="atms_view">Terminales:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="atms_view" name="atms_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
        
                                    <div class="col-md-2">
                                        <label for="business_view">Empresas:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="business_view" name="business_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2" style="display: none">
                                        <label for="branch_by_terminal_view">Sucursal por terminal:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="branch_by_terminal_view" name="branch_by_terminal_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="col-md-2" style="display: none">
                                        <label for="terminal_per_branch_view">Terminales por sucursal:</label>
                                        <div class="form-group">
                                            <div style="border: 1px solid #d2d6de; padding: 5px">
                                                <input id="terminal_per_branch_view" name="terminal_per_branch_view" type="checkbox"
                                                    class="form-control checkbox icheck">
                                                &nbsp; Ver / Ocultar
                                            </div>
                                        </div>
                                    </div>
        
                                </div>

                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <div id="map" style="width:100%; height: 40vh; border: 3px solid orange; border-radius: 5px; background: white"></div>
                                    </div>
                                </div>
        
                            </div>
                              
                        </div>
                
                  
                
                    </div>
                </div>
                 --}}

                {!! Form::open(['route' => 'atmhascampagins.store' , 'method' => 'POST', 'role' => 'form','id' => 'nuevaAsociacion-form']) !!}
                <div class="modal-body">
                    <div class="box-body">
                

                        @include('partials._messages')
                        <div class="row">
                            {!! Form::hidden('campaign_id',$campaign_id) !!}

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('atm_id', 'ATM') !!} 
                                    {!! Form::select('atm_id', $atms_list, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                </div>
                            </div>

                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('provider_id', 'Proveedor de promociones') !!}
                                    {!! Form::select('provider_id', $providers, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                </div>
                            </div> --}}
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('business_id', 'Negocio') !!}
                                    {!! Form::select('business_id', $business_list, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('branch_id', 'Sucursal para retirar') !!}
                                    {!! Form::select('branch_id', $branches, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary has-spinner" id="btnAsociar">Asociar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

    </div>
</div>
<style type="text/css">
    /*se agranda el modal para poder cargar el map*/
    @media screen and (min-width: 1200px){
        .modal-xl>.modal-dialog{
            width: 1200px;
        }
    }
    
</style>

@section('page_scripts')
    {{-- <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script> --}}
         <!-- select2 -->
         <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
         <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
     
@endsection