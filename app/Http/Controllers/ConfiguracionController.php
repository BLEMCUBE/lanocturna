<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodigoMaestroUpdateRequest;
use App\Models\Configuracion;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        /*$configuraciones=Configuracion::all();
        return Inertia::render('Configuracion/Index', [
            'configuraciones' => $configuraciones
        ]);*/
        $codigo_maestro=Configuracion::where('slug','codigo-maestro')->first();
        //return $codigo_maestro;
        return Inertia::render('Configuracion/EditarCodigoMaestro', [
            'codigo_maestro' => $codigo_maestro
        ]);

    }

    public function update(CodigoMaestroUpdateRequest $request, $id)
    {
        $codigo = Configuracion::find($id);

        $codigo->value =Hash::make( $request->codigo);
        $codigo->save();

    }




}
