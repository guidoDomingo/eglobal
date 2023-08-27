@extends('mails.base')

@section('content')
    <style>
        .title {
            margin-top: 0;
            color: #565656;
            font-weight: 700;
            font-size: 36px;
            Margin-bottom: 18px;
            font-family: sans-serif;
            line-height: 42px;
            text-align: center
        }

        .sub_title {
            margin-top: 0;
            color: #565656;
            font-family: Georgia, serif;
            font-size: 16px;
            line-height: 25px;
        }

        .link {
            display: block;
            width: 100%;
            border: 1px solid #565656;
            border-radius: 5px;
            padding: 5px;
            text-align: center;
            margin-bottom: 10px;
        }

        .container {
            display: block;
            border: 1px solid #565656;
            border-radius: 5px;
            padding: 5px;
        }

    </style>

    <h2 class="title"> {{ $title }} </h2>

    <div class="container">
        <p class="sub_title"> {{ $sub_title }} </p>
        <p class="sub_title"> 
            En el caso que el link no se abra al hacer click, 
            hacer click derecho en el link y elegir la opción: 
            <b>Abrir vínculo o enlace en una pestaña nueva </b>
        </p>

        <!--<p class="sub_title"> 
            Al abrir archivo seleccionar opción: <br /> 
            1) Datos <br />  
            2) Texto en columnas <br />
            3) Delimitadores <br />
            4) Siguiente <br />
            5) Seleccionar separador: Coma <br />
            6) Siguiente <br />
            7) Formato de los datos en columnas: General <br />
            8) Finalizar <br />
        </p>-->
    </div>

    <br />

    <div class="container">
        @for ($i = 0; $i < count($files); $i++)
            <?php
            $name = $files[$i]['name'];
            $url_link = $files[$i]['url_link'];
            ?>

            <a class="link" href="{{ $url_link }}" target="_blank"> Link al archivo de {{ $name }} </a>
            <br />
        @endfor
    </div>

    <br />
@stop