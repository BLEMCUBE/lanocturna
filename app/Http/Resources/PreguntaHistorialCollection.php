<?php

namespace App\Http\Resources;

use App\Helpers\HelperMercadoLibre;
use App\Services\ItemService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PreguntaHistorialCollection extends ResourceCollection
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
				$pregunta = $row->payload;
				$publicado=Carbon::parse($pregunta['date_created']);
				$user = $row->from_user->payload??null;
				$product=app(ItemService::class)->detalle($row->item_id);
				return [
					'id' => $row->id,
					'pregunta' => $row->text,
					"date_created" => Carbon::parse($pregunta['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
					'publicado'=>$publicado->diffForHumans(),
					'mercadolibre_pregunta_id'=>$row->mercadolibre_pregunta_id,
					'from_user_id'=>$row->from_user_id,
					'producto' =>$product,
					'respuesta' =>$row['respuesta']['payload']['text'],
					'fecha_respuesta' =>Carbon::parse($row['respuesta']['payload']['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
					'usuario' => [
						'nickname' => !is_null($user)?$user['nickname']:'',
						'permalink' => !is_null($user)?$user['permalink']:'',
						'city' =>  !is_null($user)?$user['address']['city']:'',
						'state' => !is_null($user) ? HelperMercadoLibre::departamento($user['address']['state']) : '',
					],

				];
			});
	}
}
