<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PagoCompraCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'data' => $this->collection->transform(function ($row, $key) {
				return [
					 'id' => $row->id,
                    'nro_factura' => $row->nro_factura??'',
                    'proveedor' => $row->proveedor??'',
                    'estado' => $row->estado??'',
                    'moneda' => $row->moneda??'',
					'pagado'=>$row->pagado,
					'total'=>$row->total,
                    //'facturador' => $row->facturador->name??'',
                    'fecha'=>Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:i:s'),
					'total_pagado' => $row->compra_pagos->sum('monto'),
					'porcentaje'=>$row->compra_pagos->sum('monto')>0&&$row->total>0?$row->compra_pagos->sum('monto') *100/$row->total :0
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
