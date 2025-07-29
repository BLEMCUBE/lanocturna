<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class PagoCompraStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

			$saldo = $this->input('saldo');
        return [
            'fecha_pago' => 'required',
            'nro_factura' => 'required|unique:pagos_compras',
            'moneda' => 'nullable',
            'concepto_pago_id' => 'nullable',
            'metodo_pago_id' => 'nullable',
			'nro_transaccion' => 'required',
			'banco' => 'required',
			'monto'=>'numeric|lte:saldo|required',

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
