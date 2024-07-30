<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogoUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
			'imagen' => 'required|image|max:2048',
        ];
    }
    public function messages()
    {
        return [
             'imagen.image' => 'Imagen debe se ser tipo imagen.',
             'imagen.required' => 'Imagen es requerido.',

        ];
    }
}
