<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'name' => 'required|unique:roles|alpha_num:ascii',
			'guard_name' => 'nullable'
		];
	}
	public function messages()
	{
		return [
			'name.required' => 'Este campo es obligatorio.',
			'name.unique' => 'El Rol ya existe.',
		];
	}
}
