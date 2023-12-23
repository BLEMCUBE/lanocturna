<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class CambiarDepositoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            //'bultos' => 'required',
            'productos.*.bultos' => 'required',
            'origen_id' => 'required',
            'destino_id' => 'required',


        ];


    }

    public function messages()
    {
        return [
            //'bultos.required' => 'Este campo es obligatorio.',
            'productos.*.cantidad.required' => 'Cantidad fila  # :position es obligatorio.',
            'destino_id.required' => 'Este campo es obligatorio.',
            'destino_id.required' => 'Este campo es obligatorio.',
        ];
    }
}
