<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'rfc'=>$this->rfc??'',
            'email' => $this->email??'',
            'telefono' => $this->telefono,
            'casa_direccion' => $this->casa_direccion??'',
            'casa_colonia' => $this->casa_colonia??'',
            'oficina_direccion' => $this->oficina_direccion??'',
            'tipo_cliente' => $this->tipo_cliente??'',
            'oficina_colonia' => $this->oficina_colonia??'',
            'total_compras' => $this->total_compras??0,
            'ultima_compra' => $this->ultima_compra??0,
            //'fecha_nacimiento'=>Carbon::createFromFormat('YYYY-mm-dd', $this->fecha_nacimiento)->format('d/m/Y'),
            'fecha_nacimiento'=>Carbon::parse($this->fecha_nacimiento)->format('d/m/Y'),
            'created_at'=>Carbon::createFromFormat('Y-m-d H:m:s', $this->created_at)->format('d/m/Y H:m:s'),


        ];
    }
}
