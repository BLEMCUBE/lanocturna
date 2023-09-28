<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DepositoHistorialCollection extends ResourceCollection
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
                $dat=json_decode($row->datos);
                return [
                    'id' => $row->id,
                    'sku'=>$dat->sku??'',
                    'producto'=>$dat->producto??'',
                    'bultos'=>$dat->bultos??'',
                    'pcs_bulto'=>$dat->pcs_bulto??'',
                    'origen'=>$dat->origen??'',
                    'destino'=>$dat->destino??'',
                    'usuario'=>$dat->usuario??'',
                    'fecha'=>Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d/m/Y H:i:s'),
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
