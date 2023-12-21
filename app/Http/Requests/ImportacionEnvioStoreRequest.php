<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ImportacionEnvioStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'destino'=> 'required',
            'archivo'=> 'required|mimes:xlsx,xls'
        ];


    }

    public function messages()
    {
        return [
            'destino.required' => 'Este campo es obligatorio.',
            'archivo.required' => 'Este campo es obligatorio.',
        ];
    }
}
