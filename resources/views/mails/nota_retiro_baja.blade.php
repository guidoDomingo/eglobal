@extends('mails.base')

@section('content')
    <h2 style="Margin-top:0;color:#565656;font-weight:700;font-size:36px;Margin-bottom:18px;font-family:sans-serif;line-height:42px;text-align:center">
       Procedimiento de baja<br> Eglobalt</h2>
      

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Hola <b style="font-weight:bold">{!! $user_name !!}</b>!</p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Se generó una nota de retiro de dispositivo. <br> Favor verificar.
    </p>
    <h3>Detalles</h3>
    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        <ul>
            <li>Identificador (ID) : {!! $identificador !!}</li>            
            <li>Fecha de la nota de retiro : {!! $fecha !!} </li>            
            <li>Ruc : {!! $ruc_cliente !!}</li>  
            <li>Cliente : {!! $nombre_cliente !!}</li>            
          
        </ul>
    </p>


@stop