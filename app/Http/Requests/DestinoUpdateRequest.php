<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class DestinoUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'nombre' => 'required',Rule::unique('destinos')->ignore($id),

        ];
    }
    public function messages()
    {
        return [


        ];
    }
}
