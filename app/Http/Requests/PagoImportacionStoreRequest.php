<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class PagoImportacionStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'fecha_pago' => 'required',
            'banco' => 'required',
            'nro_transaccion' => 'required',
            'id' => 'required',
			'monto'=>'numeric|required',


        ];


    }

    public function messages()
    {
        return [
            'fecha_pago.required' => 'Este campo es obligatorio.',
            'banco.required' => 'Este campo es obligatorio.',
            'nro_transaccion.required' => 'Este campo es obligatorio.',
            'monto.required' => 'Este campo es obligatorio.',
            'id.required' => 'Este campo es obligatorio.',
        ];
    }
}
