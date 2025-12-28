<?php

namespace App\Http\Resources;

use App\Services\MercadoLibre\ItemService;
use App\Services\MercadoLibre\OrdenService;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;


class MLVentaResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		$jOrden = $this->payload;
		$jEnvio = $this->envio;
		$jFacturacion = $this->facturacion;
		$comprador = app(OrdenService::class)->comprador($this->id);

		$orderItems = $jOrden['order_items'];
		$orderPagos = $jOrden['payments'];
		$productos = [];
		foreach ($orderItems as $key => $it) {
			$ii = app(ItemService::class)->detalle($this->item_ids[0], false);
			if (!empty($ii)) {
				$attrItem = collect($it['item']['variation_attributes'])->map(fn($attr) => $attr['name'] . ': ' . $attr['value_name'])->implode(', ');
				array_push($productos, [
					"titulo" => $it['item']['title'],
					"sku" => $it['item']['seller_sku'],
					"cantidad" => $it['quantity'],
					"precio" => $it['unit_price'],
					"atributos" => $attrItem,
					"imagen" => $ii['thumbnail']
				]);
			}
		}

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
				$costo_envio=$jEnvio['shipping_option']['list_cost']??0;
				$total=array_sum(array_column($productos, 'precio'));
				$costo_pagos=array_sum(array_column($pagos, 'marketplace_fee'));
				$pagado=$this->shipping_paid_by;
				$totalp=$pagado=='VENDEDOR'?$total-$costo_envio-$costo_pagos:$total-$costo_pagos;

		return [
			'pagos' => $pagos,
			'fecha' => \Carbon\Carbon::parse($jOrden['date_created'])->setTimezone(config('app.timezone'))->format("d-m-Y H:i") ?? '',
			'orden_id' => $this->orden_id,
			'pack_id' => $this->pack_id??'',
			'estado' => $this->status,
			'pagado' => $pagado,
			'total' =>  number_format($total,2,',','.')??0,
			'total_final'=>number_format($totalp,2,',','.'),

			'comprador' => [

				'nombre' => $comprador['first_name'].' '.$comprador['last_name']??'',
				'doc' => $jFacturacion['billing_info']['doc_number']??'',
				'doc_type' =>$jFacturacion['billing_info']['doc_type']??'',
				'user' => $comprador['nickname']??'',
			],
			"envio"=>[
				"id"=>$jEnvio['id']??'',
				"tracking_number"=>$jEnvio['tracking_number']??'',
				"status"=>$jEnvio['status']??'',
				"list_cost"=>number_format($costo_envio,2,',','.')??0,
				"city"=>$jEnvio['receiver_address']['city']['name']??'',
				"state"=>$jEnvio['receiver_address']['state']['name']??'',
				"street_name"=>$jEnvio['receiver_address']['street_name']??'',
				"address_line"=>$jEnvio['receiver_address']['address_line']??'',
				"receiver_name"=>$jEnvio['receiver_address']['receiver_name']??'',
				"zip_code"=>$jEnvio['receiver_address']['zip_code']??'',
				"comment"=>$jEnvio['receiver_address']['comment']??'',

			],

			'productos' => $productos


		];
	}
}
