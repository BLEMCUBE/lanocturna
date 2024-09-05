<table>
    <thead>
    <tr >
        <th style="background-color: #d9d9d9;text-align:center">fecha</th>
        <th style="background-color: #d9d9d9;text-align:center">factura</th>
        <th style="background-color: #d9d9d9;text-align:center">moneda</th>
        <th style="background-color: #d9d9d9;text-align:center">monto</th>
		<th style="background-color: #d9d9d9;text-align:center">concepto</th>
		<th style="background-color: #d9d9d9;text-align:center">observacion</th>
    </tr>
    </thead>

    <tbody>
    @foreach($servicios as $importacion)
        <tr>
            <td>{{ $importacion->fecha??'' }}</td>
            <td style="text-align:right">{{ strval( $importacion->nro_factura)??'' }}</td>
            <td>{{ $importacion->moneda??'' }}</td>
            <td style="text-align:right">{{ $importacion->monto??'' }}</td>
            <td>{{ $importacion->tconcepto??'' }}</td>
            <td>{{ $importacion->observacion??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
