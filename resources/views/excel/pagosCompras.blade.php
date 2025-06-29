<table>
    <thead>
    <tr >
        <th style="background-color: #d9d9d9;text-align:center">factura</th>
        <th style="background-color: #d9d9d9;text-align:center">fecha</th>
		<th style="background-color: #d9d9d9;text-align:center">nro_transaccion</th>
		<th style="background-color: #d9d9d9;text-align:center">banco</th>
        <th style="background-color: #d9d9d9;text-align:center">monto</th>
    </tr>
    </thead>

    <tbody>
    @foreach($importaciones as $importacion)
        <tr>
            <td>{{ strval( $importacion->nro_factura)??'' }}</td>
            <td>{{ $importacion->fecha??'' }}</td>
            <td>{{ strval($importacion->nro_transaccion)??'' }}</td>
            <td style="text-align:right">{{ $importacion->banco ??'' }}</td>
            <td style="text-align:right">{{ $importacion->monto??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
