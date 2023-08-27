@extends('layout')
@section('title')
    Inicio
@endsection
@section('content')
    <section class="content-header">
        <h1>
            General
            <small>Dashboard</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')


    </section>
@endsection
@section('page_scripts')
@endsection