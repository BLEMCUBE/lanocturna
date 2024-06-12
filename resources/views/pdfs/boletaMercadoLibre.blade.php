<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <style>
        .btl {
            border-top: 0.5pt solid black;
            border-right: none;
            border-bottom: none;
            border-left: 0.5pt solid black;
        }

        .bt {
            border-top: 0.5pt solid black;
            border-right: none;
            border-bottom: none;
            border-left: none;
        }

        .bb {
            border-top: none;
            border-right: none;
            border-bottom: 0.5pt solid black;
            border-left: none;
        }

        .brb {
            border-top: none;
            border-right: 0.5pt solid black;
            border-bottom: 0.5pt solid black;
            border-left: none;
        }

        .bl {
            border-top: none;
            border-right: none;
            border-bottom: none;
            border-left: 0.5pt solid black;
        }

        .bbl {
            border-top: none;
            border-right: none;
            border-bottom: 0.5pt solid black;
            border-left: 0.5pt solid black;
        }

        .bbr {
            border-top: none;
            border-right: 0.5pt solid black;
            border-bottom: 0.5pt solid black;
            border-left: none;
        }

        .btr {
            border-top: 0.5pt solid black;
            border-right: 0.5pt solid black;
            border-bottom: none;
            border-left: none;
        }

        .br {
            border-top: none;
            border-right: 0.5pt solid black;
            border-bottom: none;
            border-left: none;
        }

        .ba {
            border: 0.5pt solid black;
        }

        body {
            font-family: Helvetica, sans-serif !important;
            font-size: 12px;
            margin: 20px;

        }

        @page {
            margin: 0 auto;

        }

        .page-break {
            page-break-after: always;
        }

        .tg {
            width: 13.5cm;
            border-collapse: collapse;
            font-family: Helvetica, sans-serif !important;
            border-spacing: 0;
            line-height: 16px;
        }


        .tg td {
            overflow: hidden;
            height: 30px;
            padding: 2px 5px;
            font-family: Helvetica, sans-serif !important;
            word-break: normal;
        }

        .tg th {
            font-family: Helvetica, sans-serif !important;
            overflow: hidden;
            padding: 3px;
            vertical-align: middle word-break: normal;
        }

        .tg .tg-chwj {
            text-align: center;
        }

        .tg-left {
            text-align: left !important
        }

        .tg-right {
            text-align: right !important
        }

        .tg-center {
            text-align: center !important
        }

        .fw-400 {
            font-weight: 400;
        }

        .fw-700 {
            font-weight: 700;
        }

        .fs-9 {
            font-size: 9px;
        }

        .fs-10 {
            font-size: 10px;
        }

        .fs-11 {
            font-size: 11px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .fs-14 {
            font-size: 14px;
        }

        .fs-16 {
            font-size: 16px;
        }

        .fs-18 {
            font-size: 18px;
        }

        .fs-20 {
            font-size: 20px;
        }

        .fs-22 {
            font-size: 22px;
        }

        .fs-24 {
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div style="text-align: center">

        <table border=0 cellpadding=0 cellspacing=0 class='tg' style="margin: 0 auto;">
            <colgroup>
                <col style='width:2.7cm;'>
                <col style='width:2.7cm;'>
                <col style='width:2.7cm;'>
                <col style='width:2.7cm;'>
                <col style='width:2.7cm;'>
            </colgroup>
            <tr>
            </tr>
            <tr>
                <td colspan="5" class="fw-700 tg-right fs-22"> <img src="{{asset('images/logo-bn.png')}}" alt="logo"
                        height="40px"></td>
            </tr>
            <tr>
                <td colspan="5" class="ba fw-700 tg-center fs-22">Recibo de entrega de producto</td>
            </tr>
            <tr>
                <td colspan="5" class="ba fw-700 tg-center fs-14">1. Datos de quien recibe el artículo</td>
            </tr>
            <tr>
                <td colspan="5" class="ba fs-10">Nombre completo:</td>
            </tr>
            <tr class="fs-10">
                <td class="ba tg-left" colspan="3">Cédula de identidad: </td>
                <td class="ba" colspan="2">Teléfono:</td>
            </tr>
            <tr>
                <td colspan="5" class="ba fw-700 tg-center fs-14">2. Información del producto</td>
            </tr>
            <tr class="fs-10">
                <td class="ba tg-center fw-700" colspan="3">Artículo </td>
                <td class="ba tg-center fw-700" colspan="2">Número de operación</td>
            </tr>
            @foreach($data['detalle'] as $key =>$item )

            <tr class="fs-12">
                <td class="ba tg-center" colspan="3">{{$item["producto"]["nombre"]}}</td>
                <td class="ba tg-center" colspan="2">{{$data["nro_compra"]}}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="ba  tg-center fs-10"> <u class="fs-14 fw-700">
                        Importante
                    </u></br>
                    Siempre corrobore la CI y firma de quien retira.
                </td>
            </tr>
            <tr class="fs-10">
                <td class="btl tg-left" colspan="3">&nbsp; </td>
                <td class="btr" colspan="2">&nbsp;</td>
            </tr>
            <tr class="fs-10">
                <td class="bl tg-left" colspan="3">&nbsp;</td>
                <td class="br" colspan="2">&nbsp;</td>
            </tr>
            <tr class="fs-10">
                <td class="bbl tg-left" colspan="3">Lugar y fecha:
                    <b class="fs-12"> {{$data["lugar"]}} -- {{$data['fecha']}} </b>
                </td>
                <td class="bbr tg-center" colspan="2">_________________________ <br />Firma</td>
            </tr>
            <tr>
                <td colspan="5" class="ba  tg-center fs-10"> <u class="fw-700 fs-14">
                        Atención
                    </u></br>
                    En caso de contracargo por parte del titular de la tarjeta, MercadoPago presentará este documento
                    pudiendo inclusive utilizarlo en eventuales medidas que se hagan necesarias para el ejercicio de sus
                    derechos.
                </td>
            </tr>
        </table>
    </div>
</body>

</html>