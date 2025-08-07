<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VentaCollection extends ResourceCollection
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
			'data' => $this->collection->transform(function ($row, $key) {
				$dat = json_decode($row->cliente);
				$parametro = json_decode($row->parametro);
				return [
					'id' => $row->id,
					//'codigo' => $row->codigo??'',
					'cliente' => $dat->nombre ?? '',
					//'empresa'=>$dat->empresa??'',
					//'rut'=>$dat->rut??'',
					'destino' => $row->destino ?? '',
					//'localidad' => $dat->localidad??'',
					//'direccion' => $dat->direccion??'',
					//'telefono' => $dat->telefono??'',
					//'total_sin_iva' => number_format($row->total_sin_iva,2)??'',
					//'vendedor' => $row->vendedor->name??'',
					//'facturador' => $row->facturador->name??'',
					//'validador' => $row->validador->name??'',
					'facturado' => $row->facturado ?? '',
					'estado' => $row->estado ?? '',
					'tipo' => $row->tipo ?? '',
					'nro_compra' => $row->nro_compra ?? '',
					'nro_orden' => $row->nro_orden ?? '',
					'mp_id' => $row->mp_id ?? '',
					'observaciones' => $row->observaciones ?? '',
					'total' => number_format($row->total, 2) ?? '',
					'parametro' => $parametro ?? [],
					//'fecha_facturacion'=>!is_null($row->fecha_facturacion)?Carbon::createFromFormat('Y-m-d H:i:s', $row->fecha_facturacion)->format('d/m/Y H:i:s'):'',
					//'fecha_validacion'=>!is_null($row->fecha_validacion)?Carbon::createFromFormat('Y-m-d H:i:s', $row->fecha_validacion)->format('d/m/Y H:i:s'):'',
					'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:i:s'),
				];
			}),
			'links' => [
				'self' => 'link-value',
			],
		];
	}
}
