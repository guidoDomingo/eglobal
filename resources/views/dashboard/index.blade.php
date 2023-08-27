@extends('layout')
@section('title')
    Dashboard - Reportes por Cajero
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Dashboard
            <small>Reportes por cajero</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
        </ol>
    </section>
    <!-- dashboard content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-12 col-sm-6 col-xs-12">
                @if($target == 'balances')
                    @include('dashboard.partials.balances_index')
                @elseif($target == 'alerts')
                    @include('dashboard.partials.alerts_index')
                @else
                    @include('dashboard.partials.conciliations_index')
                @endif
            </div>
        </div>
        <!-- Info boxes -->
    </section>
@endsection