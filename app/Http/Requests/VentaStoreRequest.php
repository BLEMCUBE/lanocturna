<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'destino' => 'required',
             'productos' => 'required',
            'cliente.nombre' => 'required',
            'cliente.direccion'=>'required_if:destino,CADETERIA|required_if:destino,DAC',
             //'metodos_pago.*.metodo_pago_id' => 'required',

        ];

    }

    public function messages()
    {
        return [
             'cliente.nombre.required' => 'Este campo es obligatorio.',
             'destino.required' => 'Este campo es obligatorio.',
             'productos.required' => 'Debe Seleccionar un producto',
        ];
    }
}
