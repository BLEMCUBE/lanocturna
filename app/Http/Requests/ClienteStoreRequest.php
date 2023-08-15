<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'nombre' => 'required',
            'telefono' => 'required',
            'email' => 'required|unique:clientes|email',
            'localidad' => 'required',
            'direccion' => 'required',
            'empresa' => 'required',
            'rut' => 'required',

        ];


    }
    public function messages()
    {
        return [
           // 'name.required' => 'Nombre es obligatorio.',

        ];
    }
}
