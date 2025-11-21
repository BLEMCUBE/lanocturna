<?php

namespace App\Http\Resources;

use App\Models\MercadoLibreMensaje;
use App\Services\ItemService;
use App\Services\MLVentaService;
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
				$comprador=app(MLVentaService::class)->comprador($row->venta->id);
				$items=app(ItemService::class)->detalle($row->venta->item_ids,true);
				return [
					'pack_id' => $row->pack_id,
					'mensaje' => $row->body,
					'hora' => $fecha->format('H:i'),
					'fecha' =>  $fecha->format('d/m/Y'),
					'productos'=>$items,
					'ult_msg'=>MercadoLibreMensaje::where('pack_id',$row->pack_id)->select('body')->latest('id')->first(),
					'comprador' => $comprador['nickname'],
					'leido'=>MercadoLibreMensaje::where('pack_id',$row->pack_id)->where('is_read',0)->where('is_from_seller',0)->count()
				];
			});
	}
}
