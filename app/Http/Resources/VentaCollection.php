<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VentaCollection extends ResourceCollection
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
				$dat = json_decode($row->cliente);
				$parametro = json_decode($row->parametro);
				return [
					'id' => $row->id,
					'cliente' => $dat->nombre ?? '',
					'destino' => $row->destino ?? '',
					'facturado' => $row->facturado ?? '',
					'estado' => $row->estado ?? '',
					'tipo' => $row->tipo ?? '',
					'nro_compra' => $row->nro_compra ?? '',
					'nro_orden' => $row->nro_orden ?? '',
					'mp_id' => $row->mp_id ?? '',
					'auth_id' => $row->auth_id ?? '',
					'observaciones' => $row->observaciones ?? '',
					'total' => number_format($row->total, 2) ?? '',
					'parametro' => $parametro ?? [],
					'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:i:s'),
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
