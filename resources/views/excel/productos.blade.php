<table>
    <thead>
    <tr style="background-color: aquamarine">
        <th>origen</th>
        <th>nombre</th>
        <th>aduana</th>
        <th>codigo_barra</th>
        <th>imagen</th>
        <th>stock</th>
        <th>stock_minimo</th>
        <th>stock_futuro</th>
        <th>arribado</th>
        <th>en_camino</th>


    </tr>
    </thead>

    <tbody>
    @foreach($productos as $producto)
        <tr>
            <td>{{ $producto->origen??'' }}</td>
            <td>{{ $producto->nombre??'' }}</td>
            <td>{{ $producto->aduana??'' }}</td>
            <td>{{ $producto->codigo_barra??'' }}</td>
            <td>{{ $producto->imagen??'' }}</td>
            <td>{{ $producto->stock??'' }}</td>
            <td>{{ $producto->stock_minimo??'' }}</td>
            <td>{{ $producto->stock_futuro??'' }}</td>
            <td>{{ $producto->arribado??'' }}</td>
            <td>{{ $producto->en_camino??'' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
