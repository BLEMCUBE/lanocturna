<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RmaStoreRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {

        return [

            'tipo' => 'required',
            'defecto' => 'required',
            'producto_id' => 'required',
            'fecha_compra' => 'required_if:tipo,RMA',
            'nro_factura' => 'required_if:tipo,RMA',
            'costo_presupuestado' => 'required_if:tipo,PRESUPUESTO',
            'cliente.nombre' => 'required',
            'cliente.telefono' => 'required_if:tipo,RMA',

        ];
    }

    public function messages()
    {
        return [
            'cliente.nombre.required' => 'Este campo es obligatorio.',
            'defecto.required' => 'Este campo es obligatorio.',
            'producto_id.required' => 'Debe seleccionar un producto.',
            'fecha_compra.required_if' => 'Este campo es obligatorio.',
            'costo_presupuestado.required_if' => 'Este campo es obligatorio.',
            'cliente.telefono.required_if' => 'Este campo es obligatorio.',
            'nro_factura.required_if' => 'Este campo es obligatorio.',

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
