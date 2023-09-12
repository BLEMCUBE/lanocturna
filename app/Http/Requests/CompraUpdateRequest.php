<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CompraUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'nro_factura' => 'required',Rule::unique('compras')->ignore($id),
            'proveedor' => 'required',
             'productos' => 'required',

        ];

    }

    public function messages()
    {
        return [
            'proveedor.required' => 'Este campo es obligatorio.',
             'nro_factura.required' => 'Este campo es obligatorio.',
             'productos.required' => 'Debe Seleccionar un producto',
       ];
    }
}
