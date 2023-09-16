<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UsuarioUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [

            'name' => 'required',
            'rol' => 'required',
            'username' => 'required|alpha_num:ascii',Rule::unique('users')->ignore($id),
            'password'=>'nullable|min:4',
            'photo' => 'image|max:2048|nullable'

        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Este campo es obligatorio.',
            'username.required' => 'Este campo es obligatorio.',
            'password.required' => 'Este campo es obligatorio.',
            'rol.required' => 'Este campo es obligatorio.',
            'photo.image' => 'Foto debe ser una imagen.',
        ];
    }
}
