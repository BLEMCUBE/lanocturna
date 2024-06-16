<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [

            'name' => ['required', Rule::unique('categorias','name')->ignore($id)]
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
