<div class="row">

    <div class="col-md-8">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('banco_id', 'Banco') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-institution"></i>
                        </div>  
                        {!! Form::select('banco_id',$bancos ,$banco_id , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('tipo_cuenta', 'Tipo de cuenta') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-institution"></i>
                        </div>  
                        {!! Form::select('tipo_cuenta',$tipo_cuentas, $tipo_cuentas_id , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%' ]) !!}
                    </div> 
                </div>
            </div>

        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('nro_cuenta', 'Nro de cuenta') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('nro_cuenta', null , ['class' => 'form-control', 'placeholder' => 'Nro de cuenta' ]) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('canal_id', 'Canal de venta') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-bullhorn"></i>
                        </div>  
                        {!! Form::select('canal_id',$canales ,$canal_id , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                    </div> 
                </div>
            </div>
        </div>

         <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('categoria_id', 'Categoria del comercio') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::select('categoria_id',$categorias ,$categoria_id , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('accesibilidad', 'Accesibilidad') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('accesibilidad', null , ['class' => 'form-control', 'placeholder' => 'Accesibilidad' ]) !!}
                    </div> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('referencia', 'Referencia') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('referencia', null , ['class' => 'form-control', 'placeholder' => 'Referencia del lugar' ]) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('visibilidad', 'Visibilidad') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('visibilidad', null , ['class' => 'form-control', 'placeholder' => 'Visibilidad' ]) !!}
                    </div> 
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('trafico', 'Trafico') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('trafico', null , ['class' => 'form-control', 'placeholder' => 'Trafico' ]) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('dueño', 'Dueño') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('dueño', null , ['class' => 'form-control', 'placeholder' => 'Dueño' ]) !!}
                    </div> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('atendido_por', 'Atendido por') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('atendido_por', null , ['class' => 'form-control', 'placeholder' => 'encargado' ]) !!}
                    </div> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('estado_pop', 'Estado en el que se encuentra el POP') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list-ul"></i>
                        </div>  
                        {!! Form::text('estado_pop', null , ['class' => 'form-control', 'placeholder' => 'Estado del POP' ]) !!}
                    </div> 
                </div>
            </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('correo', 'Correo') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-envelope-o"></i>
                            </div>  
                            {!! Form::email('correo', null , ['class' => 'form-control', 'placeholder' => 'Correo del cliente' ]) !!}
                        </div> 
                    </div>
                </div>
        </div>     

    </div>

    <div class="col-md-3" style="border:solid 1px; padding: 10px;  border-radius: 15px">
        <i class="fa fa-check-square-o" aria-hidden="true"></i>                                
        {!! Form::label('cuestionario', 'Cuestionario',['style' => 'font-weight:bold']) !!}                

        <div class="form-check">
            {!! Form::checkbox('permite_pop', 'si', false) !!}
            {!! Form::label('permite_pop', 'Permite POP ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_pop', 'si', false) !!}
            {!! Form::label('tiene_pop', ' Tiene POP ?') !!}
        </div>
        <div class="form-check">
            {!! Form::checkbox('tiene_bancard', 'si', false) !!}
            {!! Form::label('tiene_bancard', 'Tiene BANCARD ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_pronet', 'si', false) !!}
            {!! Form::label('tiene_pronet', ' Tiene PRONET ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_netel', 'si', false) !!}
            {!! Form::label('tiene_netel', 'Tiene NETEL?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_pos_dinelco', 'si', false) !!}
            {!! Form::label('tiene_pos_dinelco', 'Tiene POS DINELCO ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_pos_bancard', 'si', false) !!}
            {!! Form::label('tiene_pos_bancard', 'Tiene POS BANCARD ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_billetaje', 'si', false) !!}
            {!! Form::label('tiene_billetaje', 'Tiene BILLETAJE ?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('tiene_telefonito', 'si', false) !!}
            {!! Form::label('tiene_telefonito', 'Tiene tm Telefonito?') !!}
        </div> 
        <i class="fa fa-sitemap" aria-hidden="true"></i>
        {!! Form::label('segmentacion', 'Segmentación de clientes', ['style' => 'font-weight:bold']) !!}                

        <div class="form-check">
            {!! Form::checkbox('visicooler', 'si', false) !!}
            {!! Form::label('visicooler', 'Cuenta con Visicooler?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('bebidas_alcohol', 'si', false) !!}
            {!! Form::label('bebidas_alcohol', 'Vende bebidas con alcohol?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('bebidas_gasificadas', 'si', false) !!}
            {!! Form::label('bebidas_gasificadas', 'Vende bebidas gasificadas?') !!}
        </div> 
        <div class="form-check">
            {!! Form::checkbox('productos_limpieza', 'si', false) !!}
            {!! Form::label('productos_limpieza', 'Vende productos de limpieza?') !!}
        </div> 

    </div>
    {!! Form::hidden('group_id', $group_id) !!}

</div>