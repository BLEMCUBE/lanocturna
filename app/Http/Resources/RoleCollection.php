<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleCollection extends ResourceCollection
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
					'user'=>count($row->users)>0?1:0,
					'permiso'=>count($row->permissions)>0?1:0

                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
