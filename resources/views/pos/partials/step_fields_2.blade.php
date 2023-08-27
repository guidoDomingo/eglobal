<div class="form-group">
    {!! Form::label('branch_id', 'Sucursal') !!} <a style="margin-left: 8em"  href='#' id="nuevaSucursal" data-toggle="modal" data-target="#modalNuevaSucursal"><small>Agregar <i class="fa fa-plus"></i></small></a>
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-sitemap"></i>
        </div> 
        {!! Form::select('branch_id',$branches ,$selected_branch , ['class' => 'form-control select2','style' => 'width: 100%','placeholder' => 'Seleccione una Sucursal...']) !!}
    </div>
</div>
@if (\Sentinel::getUser()->inRole('atms_v2.area_comercial'))
    <div class="form-group">
        {!! Form::label('pos_code', 'Código de Sucursal') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-keyboard-o"></i>
            </div> 
            {!! Form::text('pos_code', '001' , ['class' => 'form-control', 'placeholder' => 'código de sucursal','readonly'=>true]) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('ondanet_code', 'Código de deposito') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-keyboard-o"></i>
            </div> 
            {!! Form::text('ondanet_code', 5000 , ['class' => 'form-control', 'placeholder' => 'código de depósito (Ondanet)','readonly'=>true]) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('description', 'Nombre') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-pencil"></i>
            </div> 
            {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ,'id' => 'description_sucursal','readonly'=>true ]) !!}
        </div>
    </div>
@else
    <div class="form-group">
        {!! Form::label('pos_code', 'Código de Sucursal') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-keyboard-o"></i>
            </div> 
            {!! Form::text('pos_code', '001' , ['class' => 'form-control', 'placeholder' => 'código de sucursal']) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('ondanet_code', 'Código de deposito') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-keyboard-o"></i>
            </div> 
            {!! Form::text('ondanet_code', 5000 , ['class' => 'form-control', 'placeholder' => 'código de depósito (Ondanet)']) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('description', 'Nombre') !!}
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-pencil"></i>
            </div> 
            {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre'  ]) !!}
        </div>
    </div>

@endif
<!-- <div class="form-group">
{!! Form::label('seller_type', 'Tipo de Vendedor') !!}
@if(isset($pointofsale))
{!! Form::select('seller_type',$ondanet_seller_types ,$selected_seller_type , ['class' => 'form-control','readonly' => 'readonly', 'placeholder' => 'Seleccione un Tipo...']) !!}
@else
{!! Form::select('seller_type',$ondanet_seller_types ,$selected_seller_type , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
@endif
</div> -->
@if(isset($deposits))
    <div class="form-group">
        {!! Form::label('deposit_code', 'Depósito relacionado:') !!}
        @foreach($deposits as $deposit)
            <p>
                <strong>Código de depósito:</strong> {{ $deposit->deposit_code }}
                <strong>Código Ondanet:</strong> {{ $deposit->ondanet_code }}
                <strong>Creado por:</strong> {{  $deposit->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($deposit->created_at)) }}
                @if($deposit->updatedBy != null)
                    <strong>Creado por:</strong> {{  $deposit->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($deposit->updated_at)) }}
                @endif
            </p>
        @endforeach
    </div>
@endif
@if(isset($pointofsale) && $pointofsale->createdBy != null)
    @if(!empty($pointofsale))
        <div class="form-group">
            {!! Form::label('created_by', 'Creado por:') !!}
            <p>{{  $pointofsale->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($pointofsale->created_at)) }}</p>
        </div>
    @endif
@endif
@if(isset($pointofsale) && $pointofsale->updatedBy != null)
    @if(!empty($pointofsale))
        <div class="form-group">
            {!! Form::label('updated_by', 'Modificado por:') !!}
            <p>{{  $pointofsale->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($pointofsale->updated_at)) }}</p>
        </div>
    @endif
@endif

