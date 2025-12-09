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
				$usuario = $row->usuario;
				$time = config('app.timezone');

				$fActual = Carbon::now($time)->format('Y-m-d H:i:s');
				$expira = 0;
				if (!is_null($usuario)) {
					$fExpira = Carbon::create($usuario->expires_at)->format('Y-m-d H:i:s');
					$fE = Carbon::parse($fExpira);
					$fA = Carbon::parse($fActual);

					if ($fE < $fA) {
						$expira = 1;
					} else {
						$expira = 0;
					}
				}
				return [
					'id' => $row->id,
					'nombre' => $row->nombre,
					'client_id' => $row->app_id,
					'client_secret' => $row->client_secret,
					'redirect_uri' => $row->redirect_uri,
					'is_default' => $row->is_default,
					'is_expired' =>  $expira,
					'expires_at' => !is_null($row->usuario) ? Carbon::createFromFormat('Y-m-d H:i:s', $row->usuario->expires_at)->format('d/m/Y H:i:s') : '',
					'usuario' => $row->usuario ? 1 : 0,
					'access_token' => $row->usuario->access_token ?? ''

				];
			});
	}
}
