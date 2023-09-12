<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompraStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'nro_factura' => 'required|unique:compras',
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
