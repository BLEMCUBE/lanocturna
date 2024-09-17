<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MetodoPagoCollection extends ResourceCollection
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
					'nombre' => $row->nombre,
					'created_at' => $row->created_at->format('d/m/Y'),
					'pagos'=>$row->pagos_servicios->count()?true:false
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
