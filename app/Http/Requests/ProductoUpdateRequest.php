<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductoUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'origen' => 'required',Rule::unique('productos')->ignore($id),
            'nombre' => 'required',Rule::unique('productos')->ignore($id),
            //'aduana' => 'required',
            'codigo_barra' => 'required',Rule::unique('productos')->ignore($id),
            //'imagen' => 'image|max:2048|nullable',
            'stock' => 'required',
            'stock_minimo' => 'required',
        ];

    }

    public function messages()
    {
        return [
            'origen.required' => 'Este campo es obligatorio.',
            'origen.unique' => 'Origen ya existe',
            'nombre.unique' => 'Nombre ya existe',
            'codigo_barra.unique' => 'Codigo barra ya existe',
            'nombre.required' => 'Este campo es obligatorio.',
            'aduana.required' => 'Este campo es obligatorio.',
            'codigo_barra.required' => 'Este campo es obligatorio.',
            'stock.required' => 'Este campo es obligatorio.',
            'stock_minimo.required' => 'Este campo es obligatorio.',

        ];
    }
}
