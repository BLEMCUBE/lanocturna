<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositoListaStoreRequest;
use App\Http\Requests\DepositoListaUpdateRequest;
use App\Http\Resources\DepositoListaCollection;
use App\Models\DepositoLista;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\DB;

class DepositoListaController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-depositoslista'])->only('index');
        //$this->middleware(['auth', 'permission:editar-depositoslista'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('DepositoLista/Index', [
            'tipo_cambio' => new DepositoListaCollection(
                DepositoLista::orderBy('id', 'ASC')
                    ->get()
            )
        ]);
    }

    public function show($id)
    {
        $deposito = DepositoLista::select('id', 'nombre', 'descripcion')
        ->findOrFail($id);


        return response()->json([
            "depositoslista" => $deposito,

        ]);
    }

    public function store(DepositoListaStoreRequest $request)
    {

        $cliente = DepositoLista::create($request->all());
    }

       public function update(DepositoListaUpdateRequest $request, $id)
    {
        $tipo_cambio = DepositoLista::findOrFail($id);
        $tipo_cambio->update($request->all());
    }


}
