<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AtributoValorCollection extends ResourceCollection
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
				return [
					'id' => $row->id,
					'valor' => $row->valor,
					'atributo_id'=>$row->atributo_id,
					'atributo_nombre'=>$row->atributo->nombre,
					'productos' => count($row->productos)>0?1:0

				];
			})	;
	}
}
