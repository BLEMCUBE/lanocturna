<table>
    <thead>
    <tr >
        <th style="background-color: #d9d9d9;text-align:center">carpeta</th>
        <th style="background-color: #d9d9d9;text-align:center">factura</th>
        <th style="background-color: #d9d9d9;text-align:center">fecha</th>
        <th style="background-color: #d9d9d9;text-align:center">monto</th>
		<th style="background-color: #d9d9d9;text-align:center">banco</th>
		<th style="background-color: #d9d9d9;text-align:center">nro_transaccion</th>
    </tr>
    </thead>

    <tbody>
    @foreach($importaciones as $importacion)
        <tr>
            <td>{{ strval( $importacion->nro_carpeta)??'' }}</td>
            <td>{{ strval( $importacion->nro_contenedor)??'' }}</td>
            <td>{{ $importacion->fecha??'' }}</td>
            <td style="text-align:right">{{ $importacion->monto??'' }}</td>
            <td style="text-align:right">{{ $importacion->banco ??'' }}</td>
            <td>{{ strval($importacion->nro_transaccion)??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
