<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtributoUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [

            'nombre' => ['required', Rule::unique('atributos','nombre')->ignore($id)]
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
