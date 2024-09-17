<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PagoServicioCollection extends ResourceCollection
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
					'fecha_pago' => !is_null($row->fecha_pago) ? Carbon::createFromFormat('Y-m-d H:i:s', $row->fecha_pago)->format('d/m/Y') : '',
					'fecha' => $row->fecha,
					'moneda' => $row->moneda ?? '',
					'nro_factura' => $row->nro_factura,
					'monto' => $row->monto,
					'observacion' => $row->observacion ?? '',
					"tconcepto"=>$row->tconcepto,
					"tmetodo"=>!is_null($row->metodo_pago)?$row->metodo_pago->nombre:'',
					"usuario"=>$row->usuario->name??'',
					'created_at' => !is_null($row->created_at) ? $row->created_at->format('d/m/Y H:i:s') : '',
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
