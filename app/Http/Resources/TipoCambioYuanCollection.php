<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TipoCambioYuanCollection extends ResourceCollection
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
                $fecha=Carbon::create($row->created_at);
                $actual=Carbon::now()->format('Y-m-d');

                return [
                    'id' => $row->id,
                    'valor'=>number_format($row->valor, 2,',', '.')??0,
                    'visible'=>$fecha->gt($actual),
                    'created_at'=>$row->created_at->format('d/m/Y'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
