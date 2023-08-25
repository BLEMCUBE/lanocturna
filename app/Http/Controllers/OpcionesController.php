<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;

class OpcionesController extends Controller
{

    public function getRoles()
    {
        $rol = auth()->user()->roles->pluck('name')[0];

        $roles=Role::where("id","!=",1)->get();
        $lista_roles = [];
        foreach ($roles as $value) {
            array_push($lista_roles, [
                'value' => $value->id,
                'label' =>  $value->name,
            ]);
        }
        return response()->json([
            'lista_roles'=>$lista_roles,
        ]);
    }


}
