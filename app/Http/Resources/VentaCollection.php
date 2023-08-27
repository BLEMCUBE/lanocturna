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
            'data' => $this->collection->transform(function($row, $key) {
                $dat=json_decode($row->cliente);
                return [
                    'id' => $row->id,
                    'codigo' => $row->codigo??'',
                    'cliente'=>$dat->nombre??'',
                    'destino' => $row->destino??'',
                    'localidad' => $dat->localidad??'',
                    'neto' => number_format($row->neto,2)??'',
                    'estado' => $row->estado??'',
                    'observaciones' => $row->observaciones??'',
                    'impuesto' => number_format($row->impuesto,2)??'',
                    'total' => number_format($row->total,2)??'',
                    'fecha'=>Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:m:s'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
