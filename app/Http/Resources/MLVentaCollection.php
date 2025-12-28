<?php

namespace App\Http\Resources;

use App\Models\MLOrden;
use App\Services\MercadoLibre\ItemService;
use App\Services\MercadoLibre\OrdenService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MLVentaCollection extends ResourceCollection
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
			'data' => $this->collection->transform(function ($r) {
				$comprador = [];
				$productos = [];
				$datos = [];

				if ($r->tipo == 'orden') {
					$comprador = app(OrdenService::class)->compradorPorOrdenId($r->venta_id);
					$orden = MLOrden::where('orden_id', $r->venta_id)
						->select('orden_id', 'payload', 'item_ids')->first();

					$payload = $orden->payload;
					$order_items = $payload['order_items'];

					if (!is_null($order_items)) {
						foreach ($order_items as $key => $it) {
							$ii = app(ItemService::class)->detalle($orden->item_ids[0], false);
							if (!empty($ii)) {
								array_push($productos, [
									"titulo" => $it['item']['title'],
									"cantidad" => $it['quantity'],
									"imagen" => $ii['thumbnail'],
									"unit_price" => $it['unit_price']
								]);
							}
						}
					}
					$datos = [
						'tipo'=> $r->tipo,
						'venta_id' => $r->venta_id,
						'estado' => $r->status,
						'created_at' => \Carbon\Carbon::parse($payload['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
						// Relaciones
						'comprador' => $comprador,
						'productos' => $productos,
						//'total' =>  number_format(array_sum(array_column($productos, 'unit_price')),)
						'total' => number_format($payload['total_amount'],2,',','.')
					];
				} else {

					$ordenes = MLOrden::where('pack_id', $r->venta_id)
						->select('orden_id', 'pack_id', 'payload', 'item_ids')->get();
					$total_pack=0;
					foreach ($ordenes as $key => $orden1) {
						$orden2 = MLOrden::where('orden_id', $orden1->orden_id)
							->select('orden_id', 'pack_id', 'payload', 'item_ids')->first();
						$comprador2 = app(OrdenService::class)->compradorPorOrdenId($orden2->orden_id);
						$payload2 = $orden2->payload;

						$order_items2 = $payload2['order_items'];

						if (!is_null($order_items2)) {
							foreach ($order_items2 as $key => $it2) {
								$ii2 = app(ItemService::class)->detalle($it2, false);
								if (!empty($ii2)) {
									array_push($productos, [
										"titulo" => $it2['item']['title'],
										"cantidad" => $it2['quantity'],
										"imagen" => $ii2['thumbnail'],
										"unit_price" => $it2['unit_price']
									]);
								}
							}
						}
						$total_pack=$total_pack+$payload2['total_amount'];

					}
					$datos = [
						'tipo'=> $r->tipo,
						'venta_id' => $r->venta_id,
						'estado' => $r->status,
						'created_at' => \Carbon\Carbon::parse($payload2['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
						// Relaciones
						'comprador' => $comprador2,
						'productos' => $productos,
						'total' =>  number_format($total_pack,2,',','.')
					];


				}

				return $datos;
			}),

			// 🔁 Paginación (igual que Laravel + Inertia)
			'meta' => [
				'current_page' => $this->currentPage(),
				'last_page'    => $this->lastPage(),
				'per_page'     => $this->perPage(),
				'total'        => $this->total(),
			],

			'links' => [
				'first' => $this->url(1),
				'last'  => $this->url($this->lastPage()),
				'prev'  => $this->previousPageUrl(),
				'next'  => $this->nextPageUrl(),
				'links' => $this->linkCollection(),
			],
		];
	}
}
