<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MLAppStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255|unique:ml_apps',
            'app_id' => 'required|string|max:255|unique:ml_apps',
            'client_secret' => 'required',
            'redirect_uri' => 'nullable',
        ];


    }
    public function messages()
    {
        return [
            'nombre.required' => 'Este campo es obligatorio.',
            'nombre.unique' => 'El nombre ya existe.',
            'app_id.required' => 'Este campo es obligatorio.',
            'app_id.unique' => 'El nombre ya existe.',
            'client_secret.required' => 'Este campo es obligatorio.',
        ];
    }
}
