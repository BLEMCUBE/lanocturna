<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'name' => 'required|unique:categorias',
        ];


    }
    public function messages()
    {
        return [
            'name.required' => 'Este campo es obligatorio.',
            'name.unique' => 'El nombre ya existe.'
        ];
    }
}
