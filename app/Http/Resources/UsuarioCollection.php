<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsuarioCollection extends ResourceCollection
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
                    'name' => $row->name,
                    'username'=>$row->username??'',
                    'photo' => $row->photo??'',
                    'status' => $row->status,
                    'last_login_at' => $row->last_login_at??'',
                    'last_login_ip' => $row->last_login_ip??'',
                    'roles'=>$row->roles->pluck('name')[0]?? '',
                    'id_rol'=>$row->roles->pluck('id')[0]?? ''

                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
