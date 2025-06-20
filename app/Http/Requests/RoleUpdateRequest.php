<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		$id = $this->input('id');
		return [
			'name' => 'required|alpha_num:ascii',
			Rule::unique('roles')->ignore($id),
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
