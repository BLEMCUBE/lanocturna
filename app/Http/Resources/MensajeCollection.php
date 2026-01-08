<?php

namespace App\Http\Resources;

use App\Models\MLMensaje;
use App\Services\MercadoLibre\ItemService;
use App\Services\MercadoLibre\OrdenService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MensajeCollection extends ResourceCollection
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
				$fecha = $row->date_created;
				$comprador=app(OrdenService::class)->comprador($row->venta->id);
				$items=app(ItemService::class)->detalle($row->venta->item_id,true);
				return [
					'pack_id' => $row->pack_id,
					'client_id' => $row->client_id,
					'mensaje' => $row->text,
					'hora' => $fecha->format('H:i'),
					'fecha' =>  $fecha->format('d/m/Y'),
					'productos'=>$items,
					'ult_msg'=>MLMensaje::where('pack_id',$row->pack_id)->select('text')->latest('date_created')->first(),
					'comprador' => $comprador['nickname'],
					'leido'=>MLMensaje::where('pack_id',$row->pack_id)->where('is_read',0)
					->where('client_id',$row->client_id)->where('is_from_seller',0)->count()
				];
			});
	}
}
