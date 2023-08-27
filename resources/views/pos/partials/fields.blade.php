<div class="form-group">
    {!! Form::label('pos_code', 'Código de Sucursal') !!}
    {!! Form::text('pos_code', null , ['class' => 'form-control', 'placeholder' => 'código de sucursal' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('ondanet_code', 'Código de deposito') !!}
    {!! Form::text('ondanet_code', null , ['class' => 'form-control', 'placeholder' => 'código de depósito (Ondanet)' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('description', 'Nombre') !!}
    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<!-- <div class="form-group">
{!! Form::label('seller_type', 'Tipo de Vendedor') !!}
@if(isset($pointofsale))
{!! Form::select('seller_type',$ondanet_seller_types ,$selected_seller_type , ['class' => 'form-control','readonly' => 'readonly', 'placeholder' => 'Seleccione un Tipo...']) !!}
@else
{!! Form::select('seller_type',$ondanet_seller_types ,$selected_seller_type , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
@endif
</div> -->

<div class="form-group">
    {!! Form::label('branch_id', 'Sucursal') !!}
    {!! Form::select('branch_id',$branches ,$selected_branch , ['class' => 'form-control','placeholder' => 'Seleccione una Sucursal...']) !!}
</div>
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
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $pointofsale->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($pointofsale->created_at)) }}</p>
    </div>
@endif
@if(isset($pointofsale) && $pointofsale->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $pointofsale->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($pointofsale->updated_at)) }}</p>
    </div>
@endif
