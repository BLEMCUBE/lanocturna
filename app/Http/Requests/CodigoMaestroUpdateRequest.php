<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CodigoMaestroUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [

            'codigo' => 'required|same:codigo2',
            'codigo2'=>'required',

        ];
    }
    public function messages()
    {
        return [
            'codigo.required' => 'Este campo es obligatorio.',
            'codigo2.required' => 'Este campo es obligatorio.',
            'codigo.same' => 'Los campos Código y Repetir código deben coincidir.',
        ];
    }
}
