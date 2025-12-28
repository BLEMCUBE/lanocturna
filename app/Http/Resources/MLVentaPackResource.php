<?php

namespace App\Http\Resources;

use App\Services\MercadoLibre\ItemService;
use App\Services\MercadoLibre\OrdenService;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;


class MLVentaPackResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{

		//$jOrden = $this->payload;
		//$jEnvio = $this->envio;
		//$jFacturacion = $this->facturacion;
		$ordenes = $this->resource; // colección

		$productos = $ordenes
			->pluck('payload')
			->pluck('order_items')
			->flatten(1)
			->groupBy(fn($it) => $it['item']['id'])
			->map(function ($items) {

				$first = $items->first();
				$ii = app(ItemService::class)->detalle($first['item']['id'], false);

				$attrItem = collect($first['item']['variation_attributes'] ?? [])
					->map(fn($attr) => $attr['name'] . ': ' . $attr['value_name'])
					->implode(', ');

				return [
					"item_id"   => $first['item']['id'],
					"titulo"    => $first['item']['title'],
					"sku"       => $first['item']['seller_sku'] ?? '',
					"cantidad"  => $items->sum('quantity'),
					"precio"    => $first['unit_price'],
					"atributos" => $attrItem,
					"imagen"    => $ii['thumbnail'] ?? '',
				];
			})
			->values();

		$total = $productos->sum(fn($p) => $p['precio'] * $p['cantidad']);

		$ordenPrincipal = $ordenes->first();
		$comprador = app(OrdenService::class)->comprador($ordenPrincipal->id);
		$orderPagos = $ordenPrincipal->payload['payments'];
		$jEnvio = $ordenPrincipal->envio;
		$jFacturacion = $ordenPrincipal->facturacion;
		$pagos = collect($orderPagos ?? [])
			->map(fn($i) => [

				"id" => $i['id'] ?? '',
				"reason" => $i['reason'] ?? '',
				"marketplace_fee" => $i['marketplace_fee'] ?? '',
				"payment_type" => $i['payment_type'] ?? '',
				"status" => $i['status'] ?? '',
				"total_paid_amount" => $i['total_paid_amount'] ?? '',

			])
			->values()
			->toArray();

		$costo_envio = $jEnvio['shipping_option']['list_cost'] ?? 0;
		$total = $productos->sum(fn($p) => $p['precio'] * $p['cantidad']);
		$costo_pagos = array_sum(array_column($pagos, 'marketplace_fee'));
		$pagado = $ordenPrincipal->shipping_paid_by;
		$totalp = $pagado == 'VENDEDOR' ? $total - $costo_envio - $costo_pagos : $total + $costo_envio - $costo_pagos;

		return [
			'pagos' => $pagos,
			'fecha' => \Carbon\Carbon::parse($ordenPrincipal['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
			'orden_id' => $ordenPrincipal->orden_id,
			'pack_id' => $ordenPrincipal->pack_id ?? '',
			'estado' => $ordenPrincipal->status,
			'pagado' => $pagado,
			'total' =>  number_format($total, 2, ',', '.') ?? 0,
			'total_final' => number_format($totalp, 2, ',', '.'),

			'comprador' => [

				'nombre' => $comprador['first_name'] . ' ' . $comprador['last_name'] ?? '',
				'doc' => $jFacturacion['billing_info']['doc_number'] ?? '',
				'doc_type' => $jFacturacion['billing_info']['doc_type'] ?? '',
				'user' => $comprador['nickname'] ?? '',
			],
			"envio" => [
				"id" => $jEnvio['id'] ?? '',
				"tracking_number" => $jEnvio['tracking_number'] ?? '',
				"status" => $jEnvio['status'] ?? '',
				"list_cost" => number_format($costo_envio, 2, ',', '.') ?? 0,
				"city" => $jEnvio['receiver_address']['city']['name'] ?? '',
				"state" => $jEnvio['receiver_address']['state']['name'] ?? '',
				"street_name" => $jEnvio['receiver_address']['street_name'] ?? '',
				"address_line" => $jEnvio['receiver_address']['address_line'] ?? '',
				"receiver_name" => $jEnvio['receiver_address']['receiver_name'] ?? '',
				"zip_code" => $jEnvio['receiver_address']['zip_code'] ?? '',
				"comment" => $jEnvio['receiver_address']['comment'] ?? '',

			],

			'productos' => $productos


		];
	}
}
