<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductoRmaCollection extends ResourceCollection
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
                $url_save = public_path() . $row->imagen;
                if (file_exists($url_save)) {
                    $image=$row->imagen;
                }else{
                    $image='/images/productos/sin_foto.png';
                }
                return [
                    'id' => $row->id,
                    'origen' => $row->origen,
                    'nombre' => $row->nombre,
                    'imagen'=>$image,
                ];
            }),
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
