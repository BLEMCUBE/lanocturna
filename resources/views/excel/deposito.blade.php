<table>
    <thead>
    <tr style="background-color: aquamarine">
        <th>sku</th>
        <th>producto</th>
        <th>pcs_bulto</th>
        <th>bultos</th>
        <th>cantidad_total</th>
        <th>deposito</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->sku??'' }}</td>
            <td>{{ $invoice->producto->nombre??'' }}</td>
            <td>{{ $invoice->pcs_bulto??'' }}</td>
            <td>{{ $invoice->bultos??'' }}</td>
            <td>{{ $invoice->cantidad_total??'' }}</td>
            <td>{{  $invoice->deposito_lista->nombre??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
