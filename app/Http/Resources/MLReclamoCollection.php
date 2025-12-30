<?php

namespace App\Http\Resources;

use App\Helpers\MercadoLibreClaimHelper;
use App\Models\MLOrden;
use App\Models\MLReclamo;
use App\Services\MercadoLibre\ItemService;
use App\Services\MercadoLibre\OrdenService;
use App\Services\MercadoLibre\ReclamoService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MLReclamoCollection extends ResourceCollection
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


				$comprador = app(OrdenService::class)->compradorPorOrdenId($r->orden_id);
				$orden = MLOrden::where('orden_id', $r->orden_id)
					->select('orden_id', 'payload', 'item_ids','status')->first();

				$reclamo = MLReclamo::where('reclamo_id', $r->reclamo_id)
				->with('mensajes')
				->select('reclamo_id', 'motivos', 'payload','detalle', 'reputacion')->first();

				$payload = $reclamo->payload;
				$order_items = $orden->payload['order_items'];

				if (!is_null($order_items)) {
					foreach ($order_items as $key => $it) {
						$ii = app(ItemService::class)->detalle($orden->item_ids[0], false);
						if (!empty($ii)) {
							array_push($productos, [
								"titulo" => $it['item']['title'],
								"cantidad" => $it['quantity'],
								"imagen" => $ii['thumbnail']
							]);
						}
					}
				}
				$datos = [
					'reclamo_id' => $r->reclamo_id,
					'estado' => $r->status,
					'fecha_creacion' => \Carbon\Carbon::parse($payload['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
					// Relaciones
					'comprador' => $comprador,
					'venta_estado'=>$orden->status,
					'productos' => $productos,
					/*
					'motivo' => MercadoLibreClaimHelper::buildReason(
						$reclamo->motivos['name']??null,
						$reclamo->motivos['detail']??null
					),
					*/
					'motivo'=>$reclamo['detalle']??[],
					'espera'=> app(ReclamoService::class)->waitingForByMessages($reclamo)
				];

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
