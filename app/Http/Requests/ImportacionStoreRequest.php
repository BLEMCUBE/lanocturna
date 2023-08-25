<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ImportacionStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'nro_carpeta' => 'required',
            'nro_contenedor' => 'required',
            'estado' => 'required',
            'archivo'=> 'required|mimes:xlsx,xls'

        ];


    }

    public function messages()
    {
        return [
            'nro_carpeta.required' => 'Este campo es obligatorio.',
            'nro_contenedor.required' => 'Este campo es obligatorio.',
            'estado.required' => 'Este campo es obligatorio.',
            'archivo.required' => 'Este campo es obligatorio.',
        ];
    }
}
