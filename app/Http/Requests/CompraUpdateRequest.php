<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
class CompraUpdateRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		$id = $this->input('id');
		return [
			'nro_factura' => 'required',
			Rule::unique('compras')->ignore($id),
			'proveedor' => 'required',
			'productos' => 'required',
			'productos.*.precio' => 'required',
			'productos.*.total' => 'nullable',
			'productos.*.cantidad' => 'required',

		];
	}

	public function messages()
	{
		return [
			'proveedor.required' => 'Este campo es obligatorio.',
			'nro_factura.required' => 'Este campo es obligatorio.',
			'productos.required' => 'Debe Seleccionar un producto',
			'productos.*.cantidad.required' => 'Cantidad fila  # :position es obligatorio.',
			'productos.*.precio.required' => 'Precio fila  # :position es obligatorio.',
			//'productos.*.total.required' => 'Total fila  # :position es obligatorio.',
		];
	}
	public function after(): array
    {

        return [
            function (Validator $validator) {
                $errores = [];
                if ($validator->errors()->has('productos.*')) {
                foreach ($validator->errors()->get('productos.*') as $key => $message) {
                    // ...
                    array_push($errores, $message[0]);
                }
                $validator->errors()->add(
                    'campos_productos',
                    $errores
                );
            }
            }
        ];
    }
}
