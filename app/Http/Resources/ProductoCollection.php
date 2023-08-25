<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductoCollection extends ResourceCollection
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
                    'nombre' => $row->nombre,
                    'origen'=>$row->origen??'',
                    'aduana' => $row->aduana??'',
                    'codigo_barra' => $row->codigo_barra??'',
                    'imagen' => $row->imagen??'',
                    'stock' => $row->stock??'',
                    'stock_minimo' => $row->stock_minimo??'',
                    'stock_futuro' => $row->stock_futuro??'',
                    'created_at'=>!is_null($row->created_at)?$row->created_at->format('d/m/Y H:i:s'):'',
                ];

            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
