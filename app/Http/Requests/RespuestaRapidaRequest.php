<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RespuestaRapidaRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'etiquetas' => 'required',
			//'etiquetas.*.titulo' => ['required',Rule::unique('respuestas_rapidas','titulo')->ignore($id)],
			'etiquetas.*.titulo' => ['required'],
			'saludo' => 'required',
			'saludo.*.value' => 'required',
			'firma' => 'required',
			'firma.*.value' => 'required'

		];
	}

	public function messages()
	{
		return [
			'saludo.required' => 'Este campo es obligatorio.',
			'firma.required' => 'Este campo es obligatorio.',
			'saludo.*.value.required' => 'El campo Saludo inicial es obligatorio',
			'firma.*.value.required' => 'El campo Firma es obligatorio',
			'etiquetas.required' => 'Debe agregar por lo menos una etiqueta',
		];
	}


	public function after(): array
	{

		return [
			function (Validator $validator) {
				$errores = [];
				if ($validator->errors()->has('etiquetas.*')) {
					foreach ($validator->errors()->get('etiquetas.*') as $key => $message) {
						// ...
						array_push($errores, $message[0]);
					}
					$validator->errors()->add(
						'campos_etiquetas',
						$errores
					);
				}

				if ($validator->errors()->has('saludo.*')) {
					foreach ($validator->errors()->get('saludo.*') as $key => $message) {
						// ...
						array_push($errores, $message[0]);
					}
					$validator->errors()->add(
						'campos_saludo',
						$errores
					);
				}

				if ($validator->errors()->has('firma.*')) {
					foreach ($validator->errors()->get('firma.*') as $key => $message) {
						// ...
						array_push($errores, $message[0]);
					}
					$validator->errors()->add(
						'campos_firma',
						$errores
					);
				}
			}
		];
	}
}
