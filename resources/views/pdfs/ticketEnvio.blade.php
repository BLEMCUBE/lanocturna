<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <style>
        @font-face {
            font-family: 'Helvetica';
            font-style: normal;
        }

        body {
            font-family: Helvetica, sans-serif;
            font-size: 13px;
            /*margin: 26px;*/
            margin: 0 auto;
        }

        @page {
            /*margin: 0 auto;*/
            margin: 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #cccccc;
            padding: 7px;
            vertical-align: middle;
        }
        .centrado {
            text-align: center;
            align-content: center;

        }

        .derecha {
            text-align: right;
            align-content: center;
        }
        .izquierdo {
            text-align: left;
            align-content: center;
        }

        .w-40 {
            width: 40%;
        }
    </style>
</head>

<body>
    <table>
        <tbody>
            <tr>
                <th class="w-40 derecha">Orden de
                    Compra:</th>
                <td class="izquierda">{{$data['nro_compra']}}</td>
            </tr>
            <tr>
                <th class="w-40 derecha">Cliente:</th>
                <td class="izquierda">{{$data['cliente']}}</td>
            </tr>
            <tr>
                <th class="w-40 derecha">Localidad:</th>
                <td class="izquierda">{{$data['localidad']}}</td>
            </tr>
            <tr>
                <th class="w-40 derecha">Dirección:</th>
                <td class="izquierda">{{$data['direccion']}} {{$data['nro_casa']}} </td>
            </tr>
            <tr>
                <th class="w-40 derecha">Télefono:</th>
                <td class="izquierda">{{$data['telefono']}}</td>
            </tr>

        </tbody>
    </table>
    <p style="font-size:12px; line-height:1rem; text-align:center">
        Gracias por tu compra!
Ante cualquier duda o inconveniente, no dudes en
contactarnos.
WhatsApp: 09236955
    </p>
</body>

</html>
