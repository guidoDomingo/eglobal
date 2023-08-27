@extends('mails.base')

@section('content')
    <h1 style="Margin-top:0;color:#565656;font-weight:700;font-size:36px;Margin-bottom:18px;font-family:sans-serif;line-height:42px;text-align:center">
        Reestablecer Contraseña</h1>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Hola <b style="font-weight:bold">{{ $user->username }}</b>!</p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        Se ha solicitado un reestablecimiento de contraseña para su cuenta de <b style="font-weight:bold">EGLOBALT</b>,
        para continuar con el proceso y establecer su nueva contraseña
        por favor visite el siguiente enlace:
    </p>

    <p style="Margin-top:0;color:#565656;font-family:Georgia,serif;font-size:16px;line-height:25px;Margin-bottom:25px">
        <a style="text-decoration:underline;color:#41637e"
           href="{{ $link }}"
           target="_blank">Reestablecer Contraseña</a></p>

@stop