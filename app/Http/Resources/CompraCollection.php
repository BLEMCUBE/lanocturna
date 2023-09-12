<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompraCollection extends ResourceCollection
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
                    'nro_factura' => $row->nro_factura??'',
                    'proveedor' => $row->proveedor??'',
                    'estado' => $row->estado??'',
                    'facturador' => $row->facturador->name??'',
                    'fecha'=>Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:i:s'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
