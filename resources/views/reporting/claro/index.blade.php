@extends('layout')
@section('title')
    Reportes
@endsection

@section('content')
<section class="content-header">
            
    <h1>
        Reportes
        <small>{{ $target }}</small>
    </h1>

    <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li><a href="#">Reportes</a></li>
    </ol>

    <br/>

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>
    @if ($target == 'Transacciones')
        @include('reporting.claro.partials.transactions_index')
    @endif
@endsection