<table>
    <thead>
    <tr style="background-color: aquamarine">
        <th>sku</th>
        <th>producto</th>
        <th>aduana</th>
        <th>precio</th>
        <th>unidad</th>
        <th>pcs_bulto</th>
        <th>bultos</th>
        <th>cantidad_total</th>
        <th>valor_total</th>
        <th>cbm_bulto</th>
        <th>cbm_total</th>
        <th>codigo_barra</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->sku }}</td>
            <td>{{ $invoice->producto->nombre }}</td>
            <td>{{$invoice->producto->aduana }}</td>
            <td>{{ $invoice->precio }}</td>
            <td>{{ $invoice->unidad }}</td>
            <td>{{ $invoice->pcs_bulto }}</td>
            <td>{{ $invoice->bultos }}</td>
            <td>{{ $invoice->cantidad_total }}</td>
            <td>{{ $invoice->valor_total }}</td>
            <td>{{ $invoice->cbm_bulto }}</td>
            <td>{{ $invoice->cbm_total }}</td>
            <td>{{ strval( $invoice->codigo_barra) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
