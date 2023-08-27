@if($credencial_6 <> null or $credencial_9 <> null )
<div class="row">
    <div class="col-md-12">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('user', 'Proveedor') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-code-fork"></i>
                    </div>
                    {!! Form::text('user',  $credencial_6->name  , ['class' => 'form-control', 'placeholder' => 'Usuario','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('user', 'Usuario') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </div>
                    {!! Form::text('user',  $credencial_6->user  , ['class' => 'form-control', 'placeholder' => 'Usuario','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('password', 'Contraseña') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-key"></i>
                    </div>
                    {!! Form::text('password', '*****' , ['class' => 'form-control', 'placeholder' => 'Contraseña','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('user', 'Proveedor') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-code-fork"></i>
                    </div>
                    {!! Form::text('user',  $credencial_9->name  , ['class' => 'form-control', 'placeholder' => 'Usuario','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('user', 'Usuario') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </div>
                    {!! Form::text('user',  $credencial_9->user  , ['class' => 'form-control', 'placeholder' => 'Usuario','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('password', 'Contraseña') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-key"></i>
                    </div>
                    {!! Form::text('password', '*****' , ['class' => 'form-control', 'placeholder' => 'Contraseña','disabled' => 'disabled' ]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <div class="form-row">
                <div class="form-group col-md-12 borderd-campaing">
                    <div class="title"  style="margin-left: 170PX;"><h4>&nbsp; Detalles - Sistemas antell &nbsp;</h4></div>
                    <div class="form-group col-md-12"  style="margin-top: 20PX;">

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor', 'Vendedor') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor', $vendedor_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_cash', 'Vendedor (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_cash', $vendedor_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Descripción') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_descripcion', $vendedor_descripcion_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Descripción (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_descripcion_cash', $vendedor_descripcion_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Déposito') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                                    {!! Form::text('deposito', $deposito_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Déposito (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                                    {!! Form::text('deposito_cash', $deposito_cash_ondanet , ['class' => 'form-control' , 'Readonly'=>'Readonly']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Caja') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                                    {!! Form::text('caja', $caja_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Caja (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                                    {!! Form::text('caja_cash', $caja_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('sucursal_descripcion', 'Sucursal') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                                    {!! Form::text('sucursal', $sucursal_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('sucursal_descripcion_cash', 'Sucursal (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                                    {!! Form::text('sucursal_cash', $sucursal_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    

</div>
    <div class="box-footer">
        <a class="btn btn-default atras" href="#step-1" role="button">Atras</a>
        <button type="submit" class="btn btn-primary btnNext" id="btnOmitirCredendiciales">Siguiente</button>
    </div>
@else
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            @if(!isset($webservices))
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('service_id', 'Proveedor') !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-sitemap""></i>
                            </div>
                            {!! Form::select('service_id',$webservices ,6 , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Proveedor...','disabled' => 'disabled']) !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>  
    </div>
    <div class="col-md-5">
        <div class="form-group">
            {!! Form::label('user', 'Usuario') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-user"></i>
                </div>
                {!! Form::text('user', null , ['class' => 'form-control', 'placeholder' => 'Usuario']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('password', 'Contraseña') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-key"></i>
                </div>
                {!! Form::text('password', null , ['class' => 'form-control', 'placeholder' => 'Contraseña' ]) !!}
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="form-group">
            <div class="form-row">
                <div class="form-group col-md-12 borderd-campaing">
                    <div class="title"  style="margin-left: 130PX;"><h4>&nbsp; Detalles - Sistemas antell &nbsp;</h4></div>
                    <div class="form-group col-md-12"  style="margin-top: 20PX;">

                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor', 'Vendedor') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor', $vendedor_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_cash', 'Vendedor (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_cash', $vendedor_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Descripción') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_descripcion', $vendedor_descripcion_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Descripción (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    {!! Form::text('vendedor_descripcion_cash', $vendedor_descripcion_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Déposito') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                                    {!! Form::text('deposito', $deposito_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Déposito (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                                    {!! Form::text('deposito_cash', $deposito_cash_ondanet , ['class' => 'form-control' , 'Readonly'=>'Readonly']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion', 'Caja') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                                    {!! Form::text('caja', $caja_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('vendedor_descripcion_cash', 'Caja (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                                    {!! Form::text('caja_cash', $caja_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('sucursal_descripcion', 'Sucursal') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                                    {!! Form::text('sucursal', $sucursal_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('sucursal_descripcion_cash', 'Sucursal (Cash)') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                                    {!! Form::text('sucursal_cash', $sucursal_cash_ondanet , ['class' => 'form-control', 'Readonly'=>'Readonly' ]) !!}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
    <div class="box-footer">
        @if (\Sentinel::getUser()->inRole('superuser'))
            <a class="btn btn-default atras" href="#step-1" role="button">Atras</a>
        @endif
        <button type="submit" class="btn btn-primary btnNext" id="btnGuardarCredencial">Guardar</button>
    </div>


@endif
 

{!! Form::hidden('abm','v2') !!} 