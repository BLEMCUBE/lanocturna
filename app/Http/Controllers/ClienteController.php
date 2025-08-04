<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;
use App\Http\Resources\ClienteCollection;
use App\Models\Cliente;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;


class ClienteController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {

        return Inertia::render('Cliente/Index', [
            'clientes' => new ClienteCollection(
                Cliente::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(ClienteStoreRequest $request)
    {

        $cliente = Cliente::create($request->all());
        //return Redirect::route('clientes.index');
    }
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json([
            "cliente" => $cliente
        ]);
    }

    public function update(ClienteUpdateRequest $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());
    }


    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        $cliente->delete();

    }
}
