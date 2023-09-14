<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductoImportacionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [

             'sku' => 'required',
             'precio' => 'required',
             'unidad' => 'required',
             'pcs_bulto' => 'required',
             'cantidad_total' => 'required',
             'valor_total' => 'required',
             'cbm_bulto' => 'required',
             'cbm_total' => 'required',
             'estado' => 'required',
             'bultos' => 'required',
             'codigo_barra' => 'required',
             'importacion_id' => 'required',

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
