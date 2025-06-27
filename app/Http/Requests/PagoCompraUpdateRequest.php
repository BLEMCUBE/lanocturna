<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagoCompraUpdateRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		$id = $this->input('id');
		return [
			'fecha_pago' => 'required',
			'nro_factura' => ['required',Rule::unique('pagos_compras')->ignore($id)],
			'moneda' => 'required',
			'concepto_pago_id' => 'required',
			'metodo_pago_id' => 'required',
			'monto' => 'numeric|required',
			'observacion' => 'nullable',

		];
	}

	public function messages()
	{
		return [
			'fecha_pago.required' => 'Este campo es obligatorio.',
			'concepto_pago_id.required' => 'Este campo es obligatorio.',
			'metodo_pago_id.required' => 'Este campo es obligatorio.',
			'nro_factura.required' => 'Este campo es obligatorio.',
			'monto.required' => 'Este campo es obligatorio.',
			'nro_factura.unique' => 'El numero de factura ya existe',
			'moneda.required' => 'Este campo es obligatorio.',
		];
	}
}
