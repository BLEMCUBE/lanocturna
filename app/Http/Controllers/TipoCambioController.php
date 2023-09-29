<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoCambioStoreRequest;
use App\Http\Requests\TipoCambioUpdateRequest;
use App\Http\Resources\TipoCambioCollection;
use App\Models\Cliente;
use App\Models\TipoCambio;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;


class TipoCambioController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-tipocambio'])->only('index');
        //$this->middleware(['auth', 'permission:crear-tipocambio'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-tipocambio'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('TipoCambio/Index', [
            'tipo_cambio' => new TipoCambioCollection(
                TipoCambio::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(TipoCambioStoreRequest $request)
    {

        $cliente = TipoCambio::create($request->all());
        //return Redirect::route('clientes.index');
    }
    public function show($id)
    {
        $cliente = TipoCambio::findOrFail($id);
        return response()->json([
            "tipo_cambio" => $cliente
        ]);
    }

    public function update(TipoCambioUpdateRequest $request, $id)
    {
        $tipo_cambio = TipoCambio::findOrFail($id);
        $tipo_cambio->update($request->all());
    }


    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        $cliente->delete();

    }
}
