<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RmaCollection extends ResourceCollection
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
                $startDate = Carbon::parse($row->fecha_ingreso);
                $endDate = Carbon::parse($row->fecha_limite);
                $actual =Carbon::now()->toDateString();
                $diffInDays = $startDate->diffInDays($actual);
                return [
                    'id' => $row->id,
                    'nro_servicio' => $row->nro_servicio??'',
                    'cliente'=>$dat->nombre??'',
                    'telefono' => $dat->telefono??'',
                    'estado' => $row->estado??'',
                    'tipo' => $row->tipo??'',
                    'modo' => $row->modo??'',
                    'dias'=>$diffInDays,
                    'fecha_ingreso'=>$row->fecha_ingreso,
                    'fecha_limite'=>$row->fecha_limite,
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
