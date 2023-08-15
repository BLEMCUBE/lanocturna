<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
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
            'name' => $this->name,
            'dni' => $this->dni,
            'codeba' => $this->codeba,
            'agencie_id' => $this->agencie_id,
            'province_id' => $this->province_id,
            'citie_id' => $this->citie_id,
            'user_id' => $this->user_id,
            'username'=>$this->user->username??'',
            'email'=>$this->user->email??'',
            'password'=>'',
            'password2'=>''

        ];
    }
}
