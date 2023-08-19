<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductoStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [

            'nombre' => 'required|unique:productos',
            'descripcion' => 'nullable',
            'categoria_id' => 'required',
            'familia_id' => 'required',
            'subproducto_id' => 'required',
            'marca_id' => 'required',
            'peso_id' => 'required',
            'longitud_id' => 'required',
            'curva_id' => 'required',
            'color_id' => 'required',
            'diferenciador_id' => 'required',
            'codigo' => 'required',
            'codigo_barra' => 'nullable',
            'stock' => 'required',
            'precio_compra' => 'required',
            'precio_venta' => 'required',
            'precio_mayorista' => 'required',
            'porcentaje_mayorista'=>'required_if:check_mayorista,1',
            'porcentaje_venta'=>'required_if:check_venta,1',
            //'check_mayorista' => 'required',
            //'check_venta' => 'required',

            'imagen_1' => 'image|max:2048|nullable'];

    }

    public function messages()
    {
        return [
             'nombre.required' => 'Nombre es obligatorio.',
             'categoria_id.required' => 'Categoría es obligatorio.',
             'familia_id.required' => 'Familia es obligatorio.',
             'subproducto_id.required' => 'Subproducto es obligatorio.',
             'marca_id.required' => 'marca es obligatorio.',
             'peso_id.required' => 'peso es obligatorio.',
             'longitud_id.required' => 'longitud es obligatorio.',
             'curva_id.required' => 'curva es obligatorio.',
             'color_id.required' => 'color es obligatorio.',
             'diferenciador_id.required' => 'diferenciador es obligatorio.',
             'porcentaje_mayorista.required_if' => 'porcentaje mayorista es obligatorio.',
             'porcentaje_venta.required_if' => 'porcentaje venta es obligatorio.',

            //'photo.image' => 'Foto debe ser una imagen.',
        ];
    }
}
