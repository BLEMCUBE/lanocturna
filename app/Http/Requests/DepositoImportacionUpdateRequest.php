<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class DepositoImportacionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
             'pcs_bulto' => 'required',
             'cantidad_total' => 'required',
             'bultos' => 'required',
        ];

    }

    public function messages()
    {
        return [

       ];
    }
}
