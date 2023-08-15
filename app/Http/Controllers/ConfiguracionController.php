<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ConfiguracionController extends Controller
{
    public function __construct()
    {
    //protegiendo el controlador segun el rol
    $this->middleware(['auth', 'permission:ver-configuraciones'])->only('index','update');

    }
    public function index()
    {
        $configuraciones=Configuracion::all();
        return Inertia::render('Configuracion/Index', [
            'configuraciones' => $configuraciones
        ]);
    }

    public function update(Request $request, $id)
    {
        $rol = Role::find($id);
        $rol->syncPermissions($request->input('permisos'));

        return Redirect::route('roles.index');
    }

    public function consultarContrasena(Request $request)
    {

    }


}
