<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'descripcion'=>$this->descripcion??'',
            'codigo_barra' => $this->codigo_barra,
            'categoria_id' => $this->categoria_id,
            'familia_id' => $this->familia_id,
            'color_id' => $this->color_id,
            'peso_id' => $this->peso_id,
            'curva_id' => $this->curva_id,
            'longitud_id' => $this->longitud_id,
            'observaciones' => $this->observaciones,
            'marca_id' => $this->marca_id,
            'diferenciador_id' => $this->diferenciador_id,
            'subproducto_id' => $this->subproducto_id,
            'stock' => $this->stock,
            'precio_venta'=>$this->precio_venta??'',
            'precio_compra'=>$this->precio_compra??'',
            'precio_mayorista'=>$this->precio_mayorista??'',
            'porcentaje_mayorista'=>$this->porcentaje_mayorista??'',
            'porcentaje_venta'=>$this->porcentaje_venta??'',
            'peso_id'=>$this->peso->codigo??'',
            'longitud_id'=>$this->longitud->codigo??'',
            'check_venta'=>$this->check_venta?true:false,
            'check_mayorista'=>$this->check_mayorista?true:false,
            'imagen_1'=>$this->imagen_1??'',
            'imagen_2'=>$this->imagen_2??'',



        ];
    }
}
