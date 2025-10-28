<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MercadoLibreCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return
			$this->collection->transform(function ($row, $key) {
				return [
					'id' => $row->id,
					'nombre' => $row->nombre,
					'client_id' => $row->client_id,
					'client_secret' => $row->client_secret,
					'redirect_uri' => $row->redirect_uri,
					'is_default' => $row->is_default,
					'usuario' => $row->usuario?1:0

				];
			})	;
	}
}
