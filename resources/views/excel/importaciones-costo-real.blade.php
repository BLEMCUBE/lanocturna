<table>
    <thead>
    <tr>
        <th>id</th>
        <th>sku</th>
        <th>costo_real</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->id??'' }}</td>
            <td>{{ $invoice->sku??'' }}</td>
            <td>{{ $invoice->costo_real??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
