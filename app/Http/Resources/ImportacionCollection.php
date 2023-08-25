<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ImportacionCollection extends ResourceCollection
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
                    'nro_carpeta' => $row->nro_carpeta,
                    'nro_contenedor'=>$row->nro_contenedor??'',
                    'estado' => $row->estado??'',
                    'total'=>number_format($row->total, 2,',', '.')??0,
                    'cbm_total'=>number_format($row->importaciones_detalles->sum('cbm_total'), 2,',', '.')??0,
                    'cantidad_productos'=>$row->importaciones_detalles->count('importacion_id'),
                    'created_at'=>!is_null($row->created_at)?$row->created_at->format('d/m/Y H:i:s'):'',
                ];

            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
