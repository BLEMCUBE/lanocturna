<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClienteUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'nombre' => 'required',
            //'telefono' => 'required',
            'email' => 'required|email', Rule::unique('clientes')->ignore($id),
            //'localidad' => 'required',
            //'direccion' => 'required',
            //'empresa' => 'required',
            //'rut' => 'required',

        ];
    }
    public function messages()
    {
        return [
            // 'name.required' => 'Nombre es obligatorio.',

        ];
    }
}
