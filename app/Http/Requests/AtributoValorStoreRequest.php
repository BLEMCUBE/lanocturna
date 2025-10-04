<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtributoValorStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'valor' => 'required|string|max:255|unique:atributo_valores',
			'atributo_id'=>'required'
        ];


    }
    public function messages()
    {
        return [
            'valor.required' => 'Este campo es obligatorio.',
            'valor.unique' => 'El valor ya existe.'
        ];
    }
}
