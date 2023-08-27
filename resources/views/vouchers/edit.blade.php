@extends('layout')

@section('title')
    Tipo de Comprobante {{ $vouchertype->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $vouchertype->description }}
            <small>Modificación de datos de Tipo de Comprobante</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('vouchers.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $vouchertype->description }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $vouchertype->description }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($vouchertype, ['route' => ['vouchers.update', $vouchertype->id ] , 'method' => 'PUT']) !!}
                        @include('vouchers.partials.fields')

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('vouchers.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>

                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        </div>
    </section>
@endsection
@section('page_scripts')
@endsection
