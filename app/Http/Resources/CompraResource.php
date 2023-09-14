<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dat = json_decode($this->cliente);
        return [
            'id' => $this->id,
            'nro_factura' => $this->nro_factura ?? '',
            'proveedor' => $this->proveedor ?? '',
            'observaciones' => $this->observaciones ?? '',
            'estado' => $this->estado ?? '',
            'facturador' => $this->facturador->name ?? '',
            'productos' => $this->detalles_compras?? [],
            'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d/m/Y H:i:s'),

        ];
    }
}
