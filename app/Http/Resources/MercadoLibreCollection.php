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
					'access_token' => $row->access_token,
					'refresh_token' => $row->refresh_token,
					'expires_at' =>$row->usuario?Carbon::parse($row->usuario->expires_at)->format('d/m/Y H:i:s') :'',
					'usuario' => $row->usuario?1:0

				];
			})	;
	}
}
