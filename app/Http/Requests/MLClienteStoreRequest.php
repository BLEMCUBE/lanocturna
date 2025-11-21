<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MLClienteStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255|unique:mercadolibre_clientes',
            'client_id' => 'required|string|max:255|unique:mercadolibre_clientes',
            'client_secret' => 'required',
            'redirect_uri' => 'nullable',
        ];


    }
    public function messages()
    {
        return [
            'nombre.required' => 'Este campo es obligatorio.',
            'nombre.unique' => 'El nombre ya existe.',
            'client_id.required' => 'Este campo es obligatorio.',
            'client_id.unique' => 'El nombre ya existe.',
            'client_secret.required' => 'Este campo es obligatorio.',
        ];
    }
}
