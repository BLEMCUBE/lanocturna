<table>
    <thead>
    <tr style="background-color: aquamarine">
        <th>fecha</th>
        <th>destino</th>
        <th>sku</th>
        <th>producto</th>
        <th>cantidad</th>
        <th>precio</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->fecha??'' }}</td>
            <td>{{ $invoice->destino??'' }}</td>
            <td>{{ $invoice->origen??'' }}</td>
            <td>{{ $invoice->nombre??'' }}</td>
            <td>{{ $invoice->cantidad??'' }}</td>
            <td>{{ $invoice->precio??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
