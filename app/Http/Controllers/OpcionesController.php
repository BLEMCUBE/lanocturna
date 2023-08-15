<?php

namespace App\Http\Controllers;


use App\Http\Requests\UsuarioStoreRequest;
use App\Http\Requests\UsuarioUpdateRequest;
use App\Http\Resources\UsuarioCollection;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;




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
