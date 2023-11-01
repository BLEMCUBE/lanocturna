<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <style>

        body {
            font-family: Helvetica, sans-serif;
            font-size: 15px;
            /*margin: 26px;*/
            margin: 0 auto;
        }

        @page {
            /*margin: 0 auto;*/
            margin: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #cccccc;
            /*padding: 7px;*/
            line-height: 25px;
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
            word-wrap: wrap break-word;
            width: 25% !important;
        }
    </style>
</head>

<body>

    <table>
        <tbody>
            <tr>
                <th class="derecha" >No. de Orden:</th>
                <td class="izquierda">{{$data['nro_servicio']}}</td>
            </tr>
            <tr>
                <th class="derecha">Fecha:</th>
                <td class="izquierda">{{$data['fecha']}}</td>
            </tr>
            <tr>
                <th class="derecha">Cliente:</th>
                <td class="izquierda">{{$data['cliente']}}</td>
            </tr>
            <tr>
                <th class="derecha">Producto:</th>
                <td class="izquierda">{{$data['producto']}}</td>
            </tr>
            <tr>
                <th class="derecha">Defecto:</th>
                <td class="izquierda">{{$data['defecto']}}</td>
            </tr>
            <tr>
                <th class="derecha">Observaciones:</th>
                <td class="izquierda">{{$data['observaciones']}}</td>
            </tr>

        </tbody>
    </table>
    <p style="font-size:10px; line-height:0.8rem; text-align:justify">
        La demora de reparación de servicio técnico en cuanto a
        productos dentro de la garantía indicada es de 10 días
        hábiles tras los cuales debe comunicarse usted mismo
        (cliente) con el servicio técnico de la empresa, en productos
        fuera de garantía ingresados con costo de reparación la
        demora es de 15 días hábiles teniendo también que
        comunicarse usted (cliente) con servicio técnico.
        No nos responsabilizamos por pérdidas de datos, así como
        de tarjetas de memoria y chip.
        Pasados los 10 días del ingreso del producto y de no
        haberse comunicado con la empresa ni levantado el
        producto, el mismo será desechado y no tendrá derecho a
        reclamar por él.

    </p>
    <b style="font-size:10px; line-height:1rem; text-align:justify">
        Celular - 092662001
        Horario - lunes a viernes de 11:00 a 17:00, sábados de
        10:30 a 13:30

    </b>
</body>

</html>
