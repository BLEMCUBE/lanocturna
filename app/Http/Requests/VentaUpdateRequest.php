<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
           'cliente.direccion'=>'required_if:destino,CADETERIA|required_if:destino,DAC',

        ];

    }

    public function messages()
    {
        return [
            'cliente.nombre.required' => 'Este campo es obligatorio.',
            'destino.required' => 'Este campo es obligatorio.',
            'cliente.direccion.required_if' => 'Este campo es obligatorio.',
            'productos.required' => 'Debe Seleccionar un producto',
       ];
    }
}
