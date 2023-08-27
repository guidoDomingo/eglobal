@extends('layout')
@section('title')
    Monitoreo - Graylog
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Monitoreo
            <small>Dashboard de Graylog</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Monitoreo</a></li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
        <div class="box-body  no-padding">
            <div class="row">
                <div class="col-xs-12">
                    <iframe id="graylog" style="width:100%;height: 900px; border: none;" src="https://eglobal.unnaki.net/graylog/search" ></iframe>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
