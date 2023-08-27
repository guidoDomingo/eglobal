<!DOCTYPE>
<html>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Nota de retiro</title>
<style>
    #contenedorPrincipal{
        width: 19cm; 
        height: 24cm;
        border: 1px solid rgb(0, 0, 0);
        margin: 10px auto;
        display: inline-block;
        padding: 15px;
    }

    #logodiv{
        display: inline-block;
        margin-left: 2em;
    }

    img{
        height: 70px;
        display: block;
        margin-left: 12em;
        margin-top: 15px;
    }

</style>

<body>
    <div id="contenedorPrincipal">
        <section>
            <div id="logodiv">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/logo-egt.png'))) }}">
            </div>
                <br><br><br>
            <p style="text-align: right" >Aunción, {{ $dia }} de {{ $mes }} del {{ $year }}</p>

            <p style="text-align: left">
                <b>SEÑOR/A.</b>
                    <br>
                    {{ $propietario }}
            </p>
            
            <p style="text-align: left">
                <b>LOCAL COMERCIAL: {{ $nombre_comercial }}.</b>
                    <br>
                    {{ $direccion }}.-
            </p>

            <p style="text-align: center">
                <b><u>Ref: {{ $referencia }}.</u></b>
            </p>
                <br>
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;A través de la presente nota autorizo suficientemente al señor <b>{{ $representante_legal }}</b> con C.I. N° <b>{{ $ruc_representante }}</b>
                para que en nombre y representación de la firma EGLOBAL S.A. con RUC N° 80043484-4 retire la  MINI TERMINAL INTELIGENTE
                propiedad de nuestra firma la cual se encuentra en su local comercial denominado <b>{{ $nombre_comercial }}</b> en fecha {{ $dia }} de {{ $mes }} del {{ $year }}.-
            </p> 
            
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;Se deja expresa constancia que el retiro de la MTI fue autorizado por usted y en común acuerdo con nosotros. Siendo así,
                usted no tiene nada más que reclamar a nuestra firma ni a sus representantes.-
            </p>
            
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;Sin otro particular y haciendo propicia la ocasión, lo saludo cordialmente.-
            </p>
                <br>

            <center>
                ____________________________
                <br>
                LUIS MARIO MACIEL RODRIGUEZ
                <br>
                APODERADO
                <br>
                EGLOBAL S.A.
            </center>
    
                <br>
            <p>
                Firma: ____________________________________
                <br>
                Aclaración: ________________________________
                <br>
                C.I.: ______________________________________
                <br>
                Fecha: ____________________________________
            </p>

              <br><br><br>
            <center>
                Casa Central: Prof. Chavez 273 c/ Dr. Bestard - Teléfono: 595 21 2376740
                <br>
                www.antell.com.py
            </center>

        </section>
    </div>
</body>
</html>
