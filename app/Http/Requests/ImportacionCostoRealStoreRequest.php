<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ImportacionCostoRealStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'archivo'=> 'required|mimes:xlsx,xls'

        ];


    }

    public function messages()
    {
        return [
            'archivo.required' => 'Debe de seleccionar un archivo.',
        ];
    }
}
