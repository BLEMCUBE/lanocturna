<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositoStoreRequest;
use App\Http\Requests\DepositoUpdateRequest;
use App\Http\Requests\TipoCambioStoreRequest;
use App\Http\Requests\TipoCambioUpdateRequest;
use App\Http\Resources\DepositoCollection;
use App\Http\Resources\TipoCambioCollection;
use App\Models\Cliente;
use App\Models\Deposito;
use App\Models\TipoCambio;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;


class DepositoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-depositos'])->only('index');
        //$this->middleware(['auth', 'permission:crear-depositos'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-depositos'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('Deposito/Index', [
            'tipo_cambio' => new DepositoCollection(
                Deposito::orderBy('id', 'ASC')
                    ->get()
            )
        ]);
    }

    public function nombres()
    {

        return Inertia::render('Deposito/Index', [
            'tipo_cambio' => new DepositoCollection(
                Deposito::orderBy('id', 'ASC')
                    ->get()
            )
        ]);
    }

    public function store(DepositoStoreRequest $request)
    {

        $cliente = Deposito::create($request->all());
    }

    public function show($id)
    {
        $cliente = Deposito::findOrFail($id);
        return response()->json([
            "tipo_cambio" => $cliente
        ]);
    }

    public function update(DepositoUpdateRequest $request, $id)
    {
        $tipo_cambio = Deposito::findOrFail($id);
        $tipo_cambio->update($request->all());
    }


    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        $cliente->delete();

    }
}
