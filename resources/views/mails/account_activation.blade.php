@extends('mails.base')

@section('content')
    <h1 style="Margin-top:0;color:#565656;font-weight:700;font-size:36px;Margin-bottom:18px;font-family:sans-serif;line-height:42px;text-align:center">
        Activación de Cuenta</h1>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Hola <b style="font-weight:bold">{{ $user->username }}</b>!</p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Bienvenido/a a <b style="font-weight:bold">EGLOBALT</b>,
        para activar su cuenta y empezar a utilizar el sistema,
        por favor visite el siguiente enlace:
    </p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        <a style="text-decoration:underline;color:#41637e"
           href="{{ $link }}"
           target="_blank">Activar Cuenta</a></p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        <b style="font-weight:bold">Contraseña: </b>{{ $password }}
    </p>
@stop