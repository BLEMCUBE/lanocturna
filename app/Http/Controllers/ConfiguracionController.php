<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodigoMaestroUpdateRequest;
use App\Http\Requests\ConfiguracionUpdateRequest;
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
     
        $codigo_maestro=Configuracion::where('slug','codigo-maestro')->first();
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

    public function webDatos()
    {
        $configuraciones=Configuracion::
        select('id','slug','key','value')
        ->whereNot('id',1)
        ->orderBy('slug')
        ->get()        
        ;
        return Inertia::render('Configuracion/Index', [
            'lista_configuracion' => 
                $configuraciones
                
        ]);

    }

    public function updateDatos(ConfiguracionUpdateRequest $request)
    {
        foreach ($request->config as $config) {
            $up_config = Configuracion::find($config['id']);
            $up_config->update([
                "value" => $config['value']
            ]);
        }
    }





}
