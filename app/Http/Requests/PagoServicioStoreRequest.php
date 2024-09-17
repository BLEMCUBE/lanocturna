<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class PagoServicioStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'fecha_pago' => 'required',
            'nro_factura' => 'required|unique:pagos_servicios',
            'moneda' => 'required',
            'concepto_pago_id' => 'required',
            'metodo_pago_id' => 'required',
			'monto'=>'numeric|required',

        ];


    }

    public function messages()
    {
        return [
            'fecha_pago.required' => 'Este campo es obligatorio.',
            'concepto_pago_id.required' => 'Este campo es obligatorio.',
            'metodo_pago_id.required' => 'Este campo es obligatorio.',
            'nro_factura.required' => 'Este campo es obligatorio.',
            'nro_factura.unique' => 'El numero de factura ya existe',
            'monto.required' => 'Este campo es obligatorio.',
            'moneda.required' => 'Este campo es obligatorio.',
        ];
    }
}
