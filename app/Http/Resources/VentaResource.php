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
        $dat = json_decode($this->cliente);
        $parametro=json_decode($this->parametro);
        return [
            'id' => $this->id,
            'cliente' => $dat->nombre ?? '',
            'direccion' => $dat->direccion ?? '',
            'empresa'=>$dat->empresa??'',
            'rut'=>$dat->rut??'',
            'telefono' => $dat->telefono ?? '',
            'localidad' => $dat->localidad ?? '',
            'codigo' => $this->codigo ?? '',
            'vendedor' => $this->vendedor->name ?? '',
            'facturador' => $this->facturador->name ?? '',
            'validador' => $this->validador->name ?? '',
            'moneda' => $this->moneda ?? '',
            'nro_compra' => $this->nro_compra ?? '',
            'tipo' => $this->tipo ?? '',
            'tipo_cambio' => $this->tipo_cambio ?? '',
            'total_sin_iva' => $this->total_sin_iva ?? '',
            'total' => $this->total ?? '',
            'parametro' => $parametro??[],
            'productos' => $this->detalles_ventas ?? [],
            'estado' => $this->estado ?? '',
            'destino' => $this->destino ?? '',
            'observaciones' => $this->observaciones ?? '',
            'fecha_facturacion' => !is_null($this->fecha_facturacion) ? Carbon::createFromFormat('Y-m-d H:i:s', $this->fecha_facturacion)->format('d/m/Y H:i:s') : '',
            'fecha_validacion' => !is_null($this->fecha_validacion) ? Carbon::createFromFormat('Y-m-d H:i:s', $this->fecha_validacion)->format('d/m/Y H:i:s') : '',
            'fecha' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('d/m/Y H:i:s'),

        ];
    }
}
