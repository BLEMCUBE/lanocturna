<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipoCambioStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'valor' => 'required',
        ];


    }
    public function messages()
    {
        return [
            'valor.required' => 'Este campo es obligatorio.',
        ];
    }
}
