<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ProductoStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'origen' => 'required|unique:productos',
            'nombre' => 'required|unique:productos',
            'aduana' => 'required',
            'codigo_barra' => 'required|unique:productos',
            'imagen' => 'image|max:2048|nullable',
            'stock' => 'required',
            'observaciones' => 'nullable',
            'stock_minimo' => 'required',
        ];



    }

    public function messages()
    {
        return [
            'origen.required' => 'Este campo es obligatorio.',
            'origen.unique' => 'Origen ya existe',
            'nombre.required' => 'Este campo es obligatorio.',
			'nombre.unique' => 'Nombre ya existe',
            'aduana.required' => 'Este campo es obligatorio.',
            'codigo_barra.required' => 'Este campo es obligatorio.',
			'codigo_barra.unique' => 'Codigo barra ya existe',
            'stock.required' => 'Este campo es obligatorio.',
            'stock_minimo.required' => 'Este campo es obligatorio.',
        ];
    }
}
