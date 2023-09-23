<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ImportacionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'nro_carpeta' => 'required',Rule::unique('importaciones')->ignore($id),
            'nro_contenedor' => 'required',
             'estado' => 'required',
             'fecha_arribado' => 'required',
             'fecha_camino' => 'required',

        ];

    }

    public function messages()
    {
        return [
            'nro_carpeta.required' => 'Este campo es obligatorio.',
            'nro_contenedor.required' => 'Este campo es obligatorio.',
            'estado.required' => 'Este campo es obligatorio.',
            'fecha_arribo.required' => 'Este campo es obligatorio.',
            'fecha_camino.required' => 'Este campo es obligatorio.',
       ];
    }
}
