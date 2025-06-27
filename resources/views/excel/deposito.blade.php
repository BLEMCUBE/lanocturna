<table>
	<thead>
		<tr style="background-color: aquamarine">
			<th>sku</th>
			<th>producto</th>
			<th>pcs_bulto</th>
			<th>bultos</th>
			<th>cantidad_total</th>
			<th>costo_real</th>
			<th>total</th>
			<th>deposito</th>
		</tr>
	</thead>

	<tbody>
		@php
		$totalG=0;
		@endphp
		@foreach($invoices as $invoice)
		@php

		$costo_r= $invoice->costos_reales !==null ? $invoice->costos_reales->monto : 0;
		$total=$invoice->costos_reales !==null? $invoice->cantidad_total *$invoice->costos_reales->monto : 0;
		$totalG=$totalG+$total;
		@endphp
		<tr>
			<td>{{ $invoice->sku??'' }}</td>
			<td>{{ $invoice->producto->nombre??'' }}</td>
			<td>{{ $invoice->pcs_bulto??'' }}</td>
			<td>{{ $invoice->bultos??'' }}</td>
			<td>{{ $invoice->cantidad_total??'' }}</td>
			<td>{{ $costo_r }}</td>
			<td>{{ $total }}</td>
			<td>{{ $invoice->deposito_lista->nombre??'' }}</td>
		</tr>
		@endforeach
		<tr>

			<td colspan="6" style="text-align: center">
				Total
			</td>
			<td>

				{{$totalG}}
			</td>
		</tr>
	</tbody>
</table>
