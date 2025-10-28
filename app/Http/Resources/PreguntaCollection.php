<?php

namespace App\Http\Resources;

use App\Helpers\HelperMercadoLibre;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PreguntaCollection extends ResourceCollection
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
				$item = $row->item->payload;
				$pregunta = $row->payload;
				$publicado=Carbon::parse($pregunta['date_created']);
				$sellerSku = collect($item['attributes'])
					->firstWhere('id', 'SELLER_SKU')['value_name'] ?? null;
				$user = $row->from_user->payload??null;

				return [
					'id' => $row->id,
					'pregunta' => $row->text,
					'publicado'=>$publicado->diffForHumans(),
					'mercadolibre_pregunta_id'=>$row->mercadolibre_pregunta_id,
					'from_user_id'=>$row->from_user_id,
					'producto' => [
						'title' => $item['title'],
						'id' => $item['id'],
						'thumbnail' => $item['thumbnail'],
						'sku' => $sellerSku,
						'permalink' => $item['permalink'],
						'base_price' => number_format($item['base_price'], 2, ',', '.') ?? 0,
						'listing_type_id' => HelperMercadoLibre::tipo($item['listing_type_id']),

					],
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
