<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtributoStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255|unique:atributos',
        ];


    }
    public function messages()
    {
        return [
            'nombre.required' => 'Este campo es obligatorio.',
            'nombre.unique' => 'El nombre ya existe.'
        ];
    }
}
