<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnvioStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'destino' => 'required',
            'nro_compra'=> 'required',
             'productos' => 'required',

        ];

    }

    public function messages()
    {
        return [
             'nro_compra.required' => 'Este campo es obligatorio.',
             'destino.required' => 'Este campo es obligatorio.',
             'productos.required' => 'Debe Seleccionar un producto',
        ];
    }
}
