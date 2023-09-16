<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class VentaUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            //'nombre' => 'required',Rule::unique('productos')->ignore($id),
            'destino' => 'required',
            'productos' => 'required',
            'cliente.nombre' => 'required',
            'productos.*.cantidad' => 'required',
            'productos.*.precio' => 'required',
            'productos.*.total' => 'required',
            'cliente.direccion' => 'required_if:destino,CADETERIA|required_if:destino,DAC',

        ];
    }

    public function messages()
    {
        return [
            'cliente.nombre.required' => 'Este campo es obligatorio.',
            'destino.required' => 'Este campo es obligatorio.',
            'productos.*.cantidad.required' => 'Cantidad fila  # :position es obligatorio.',
            'productos.*.precio.required' => 'Precio fila  # :position es obligatorio.',
            'productos.*.total.required' => 'Total fila  # :position es obligatorio.',
            'cliente.direccion.required_if' => 'Este campo es obligatorio.',
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
