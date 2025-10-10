<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductoVentaCollection extends ResourceCollection
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
					'origen' => $row->origen,
					'nombre' => $row->nombre,
					'precio' => $row->precio,
					'observaciones' => $row->observaciones,
					'imagen' => $row->imagen ?? '',
					'stock' => $row->stock ?? '',
					'importacion_detalles' => count($row->importacion_detalles) > 0 ? $row->importacion_detalles : [],
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
