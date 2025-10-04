<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AtributoListaCollection extends ResourceCollection
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
				$valores = [];

				if ($row->valores) {
					foreach ($row->valores as $key => $valor) {
						# code...
						array_push($valores, [
							'id' => $valor->id,
							'valor' => $valor->valor,
							'nombre' => $row->nombre,
							'atributo_id' => $row->id,
						]);
					}
				}
				return [
					'id' => $row->id,
					'nombre' => $row->nombre,
					'valores' => $valores

				];
			});
	}
}
