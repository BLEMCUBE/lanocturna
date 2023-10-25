<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class SubirRmaStoreRequest extends FormRequest
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
             'productos.*.cantidad' => 'required',

        ];

    }

    public function messages()
    {
        return [
             'destino.required' => 'Este campo es obligatorio.',
             'productos.*.cantidad.required' => 'Cantidad fila  # :position es obligatorio.',
             'productos.required' => 'Debe Seleccionar un producto',
        ];
    }

    public function after(): array
    {

        return [
            function (Validator $validator) {
                $errores = [];
                if ($validator->errors()->has('productos.*')) {
                foreach ($validator->errors()->get('productos.*') as $key => $message) {
                    // ...
                    array_push($errores, $message[0]);
                }
                $validator->errors()->add(
                    'campos_productos',
                    $errores
                );
            }
            }
        ];
    }
}
