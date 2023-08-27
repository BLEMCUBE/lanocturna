<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinoStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'nombre' => 'required|unique:destinos',
        ];
    }
    public function messages()
    {
        return [

        ];
    }
}
