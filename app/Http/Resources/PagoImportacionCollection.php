<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PagoImportacionCollection extends ResourceCollection
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
					'nro_carpeta' => $row->nro_carpeta,
					'estado' => $row->estado ?? '',
					//'costo_cif'=>number_format($row->costo_cif, 2,',', '.')??0,
					//'saldo'=>number_format($row->importaciones_pagos->sum('monto'), 2,',', '.')??0,
					'costo_cif' => $row->costo_cif ?? 0,
					'nro_contenedor' => $row->nro_contenedor,
					'pagado' => $row->importaciones_pagos->sum('monto'),
					'porcentaje'=>$row->importaciones_pagos->sum('monto')>0&&$row->costo_cif>0?$row->importaciones_pagos->sum('monto') *100/$row->costo_cif :0,
					'fecha_arribado' => !is_null($row->fecha_arribado) ? Carbon::createFromFormat('Y-m-d H:i:s', $row->fecha_arribado)->format('d/m/Y') : '',
					'created_at' => !is_null($row->created_at) ? $row->created_at->format('d/m/Y H:i:s') : '',
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
