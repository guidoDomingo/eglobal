@if(isset($vendedor_ondanet) && $vendedor_ondanet != null)
     <div class="form-row">
        <div class="form-group col-md-12 borderd-campaing">
            <div class="title"><h4>&nbsp; ONDANET&nbsp;</h4></div>
            <div class="form-group col-md-12"  style="margin-top: 20PX;">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('vendedor', 'Vendedor') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::number('vendedor', null , ['class' => 'form-control', 'placeholder' => 'Número del vendedor ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('vendedor_descripcion', 'Descripción') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::text('vendedor_descripcion', null , ['class' => 'form-control', 'placeholder' => 'Descripción del vendedor ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('deposito', 'Depósito') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                            {!! Form::text('deposito', null , ['class' => 'form-control', 'placeholder' => 'Depósito ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('caja', 'Caja') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                            {!! Form::text('caja', null , ['class' => 'form-control', 'placeholder' => 'Caja ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sucursal', 'Sucursal') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                            {!! Form::text('sucursal', null , ['class' => 'form-control', 'placeholder' => 'Sucursal ondanet' ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12 borderd-campaing">
            <div class="title" ><h4>&nbsp; ONDANET CASH &nbsp;</h4></div>
            <div class="form-group col-md-12" style="margin-top: 20PX;">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('vendedor_cash', 'Vendedor (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::number('vendedor_cash', null , ['class' => 'form-control', 'placeholder' => 'Número del vendedor ondanet cash' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('vendedor_descripcion_cash', 'Descripcion (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::text('vendedor_descripcion_cash', null , ['class' => 'form-control', 'placeholder' => 'Descripción del vendedor ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('deposito_cash', 'Depósito (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                            {!! Form::text('deposito_cash', null , ['class' => 'form-control', 'placeholder' => 'Depósito ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('caja_cash', 'Caja (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                            {!! Form::text('caja_cash', null , ['class' => 'form-control', 'placeholder' => 'Caja ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sucursal_cash', 'Sucursal (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                            {!! Form::text('sucursal_cash', null , ['class' => 'form-control', 'placeholder' => 'Sucursal ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@else
    <div class="form-row">
        <div class="form-group col-md-12 borderd-campaing">
            <div class="title"><h4>&nbsp; ONDANET &nbsp;</h4></div>
            <div class="form-group col-md-12"  style="margin-top: 20PX;">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('vendedor', 'Vendedor') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::number('vendedor', null , ['class' => 'form-control', 'placeholder' => 'Número del vendedor ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('vendedor_descripcion', 'Descripción') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::text('vendedor_descripcion', null , ['class' => 'form-control', 'placeholder' => 'Descripción del vendedor ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('deposito', 'Depósito') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                            {!! Form::text('deposito', 5000 , ['class' => 'form-control', 'placeholder' => 'Depósito ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('caja', 'Caja') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                            {!! Form::text('caja', 5000 , ['class' => 'form-control', 'placeholder' => 'Caja ondanet' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sucursal', 'Sucursal') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                            {!! Form::text('sucursal', 9169 , ['class' => 'form-control', 'placeholder' => 'Sucursal ondanet' ]) !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-12 borderd-campaing">
            <div class="title" ><h4>&nbsp; ONDANET CASH &nbsp;</h4></div>
            <div class="form-group col-md-12" style="margin-top: 20PX;">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('vendedor_cash', 'Vendedor (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::number('vendedor_cash', null , ['class' => 'form-control', 'placeholder' => 'Número del vendedor ondanet cash' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('vendedor_descripcion_cash', 'Descripcion (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            {!! Form::text('vendedor_descripcion_cash', null , ['class' => 'form-control', 'placeholder' => 'Descripción del vendedor ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('deposito_cash', 'Depósito (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-cc"></i></div>
                            {!! Form::text('deposito_cash', 6000 , ['class' => 'form-control', 'placeholder' => 'Depósito ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('caja_cash', 'Caja (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-object-group"></i></div>
                            {!! Form::text('caja_cash', 5000 , ['class' => 'form-control', 'placeholder' => 'Caja ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('sucursal_cash', 'Sucursal (Cash)') !!}
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-share-alt"></i></div>
                            {!! Form::text('sucursal_cash', 9169 , ['class' => 'form-control', 'placeholder' => 'Sucursal ondanet (cash)' ]) !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif
{!! Form::hidden('abm','v2') !!} 
