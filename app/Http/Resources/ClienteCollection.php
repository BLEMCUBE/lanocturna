<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClienteCollection extends ResourceCollection
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
                    'telefono' => $row->telefono,
                    'email' => $row->email??'',
                    'localidad'=>$row->localidad??'',
                    'direccion' => $row->direccion??'',
                    'empresa' => $row->empresa??'',
                    'rut' => $row->rut??'',


                    //'fecha_nacimiento'=>Carbon::parse($row->fecha_nacimiento)->format('d/m/Y'),
                    'created_at'=>$row->created_at->format('d/m/Y H:m:s'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
