<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class ConfiguracionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [

            'config.*.value' => 'required',

        ];
    }
    public function messages()
    {
        return [
            'config.*.value.required' => 'Este campo es obligatorio.',
        ];
    }

    public function after(): array
    {

        return [
            function (Validator $validator) {
                $errores = [];
                if ($validator->errors()->has('config.*')) {
                foreach ($validator->errors()->get('config.*') as $key => $message) {
                    // ...
                    array_push($errores, $message[0]);
                }
                $validator->errors()->add(
                    'campos_config',
                    $errores
                );
            }
            }
        ];
    }
}
