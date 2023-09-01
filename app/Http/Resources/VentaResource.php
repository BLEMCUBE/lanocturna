<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class VentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dat=json_decode($this->cliente);
        return [
            'id' => $this->id,
            'cliente' => $dat->nombre,
            'codigo' => $this->codigo,
            'vendedor' => $this->vendedor->name,
            'moneda' => $this->moneda,
            'tipo_cambio' => $this->tipo_cambio,
            'total_sin_iva' => $this->total_sin_iva,
            'total' => $this->total,
            'productos' => $this->detalles_ventas,
            'estado' => $this->estado,
            'destino'=>$this->destino??'',
            'fecha'=>Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d/m/Y H:i:s'),

        ];
    }
}
