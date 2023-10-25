<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class RmaResource extends JsonResource
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
            'nro_servicio' => $this->nro_servicio ?? '',
            'cliente' => $dat->nombre ?? '',
            'telefono' => $dat->telefono ?? '',
            'vendedor' => $this->vendedor->name ?? '',
            'nro_compra' => $this->nro_compra ?? '',
            'nro_factura' => $this->nro_factura ?? '',
            'vendedor_id' => $this->vendedor_id ?? '',
            'tipo' => $this->tipo ?? '',
            'modo' => $this->modo ?? '',
            'estado' => $this->estado ?? '',
            'producto_id' => $this->producto_id ?? '',
            'prod_cantidad' => $this->prod_cantidad ?? '',
            'imagen' => $this->producto->imagen ?? '/images/productos/sin_foto.png',
            'stock' => $this->producto->stock,
            'prod_origen' => $this->prod_origen ?? '',
            'prod_nombre' => $this->prod_nombre ?? '',
            'prod_serie' => $this->prod_serie ?? '',
            'costo_presupuestado' => $this->costo_presupuestado ?? '',
            'observaciones' => $this->observaciones ?? '',
            'defecto' => $this->defecto ?? '',
            'fecha_ingreso'=>$this->fecha_ingreso??null,
            'fecha_limite'=>$this->fecha_limite??null,
            'fecha_entrega'=>$this->fecha_entrega??null,
            'fecha_compra'=>$this->fecha_compra??null,

        ];
    }

}
