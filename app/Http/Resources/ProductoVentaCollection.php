<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductoVentaCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function($row, $key) {
                return [
                    'id' => $row->id,
                    'origen' => $row->origen,
                    'nombre' => $row->nombre,
                    'imagen'=>$row->imagen??'',
                    'stock' => $row->stock??''
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
