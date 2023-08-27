<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class VentaUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            //'nombre' => 'required',Rule::unique('productos')->ignore($id),
            'user_id' => 'required',
            'cliente_id' => 'required',
            'codigo' => 'required',
            'productos' => 'required',
            'tipo_pago' => 'required',
            'metodo_pago' => 'required',
            'forma_entrega' => 'required',
            'monto_efectivo'=>'required_if:metodo_pago_id,1|required_if:metodo_pago_id,3',
            'monto_tarjeta'=>'required_if:metodo_pago_id,2|required_if:metodo_pago_id,3',
            'num_tarjeta'=>'required_if:metodo_pago_id,2|required_if:metodo_pago_id,3',
            //'metodos_pago.*.metodo_pago_id' => 'required',

        ];

    }

    public function messages()
    {
        return [
            'cliente_id.required' => 'Este campo es obligatorio.',
            'metodo_pago.required' => 'Este campo es obligatorio.',
            'monto_efectivo.required_if' => 'Este campo es obligatorio.',
            'num_tarjeta.required_if' => 'Este campo es obligatorio.',
            'monto_tarjeta.required_if' => 'Este campo es obligatorio.',
            'productos.required' => 'Debe Seleccionar un producto',
       ];
    }
}
