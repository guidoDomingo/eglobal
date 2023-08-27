@extends('mails.base')

@section('content')
    <h2 style="Margin-top:0;color:#565656;font-weight:700;font-size:36px;Margin-bottom:18px;font-family:sans-serif;line-height:42px;text-align:center">
        Notificacion - Eglobalt</h2>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Hola <b style="font-weight:bold">{{ $user_name }}</b>!</p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Se registró un inconveniente al procesar Cobranzas Miniterminales
    </p>
    <h3>Detalles</h3>
    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        <ul>
            <li>Fecha de la Boleta : {{ $fecha  }}</li>            
            <li>Identificador : {{ $boleta }}</li>            
            <li>Número de Boleta : {{ $nroboleta }}</li>
            <li>Monto : {{ $monto }}</li>            
        </ul>
    </p>


@stop