<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtributoValorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [

            'valor' => ['required', Rule::unique('atributo_valores','valor')->ignore($id)],
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
