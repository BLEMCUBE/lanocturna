<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DestinoCollection extends ResourceCollection
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
                    'created_at'=>Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:m:s'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
