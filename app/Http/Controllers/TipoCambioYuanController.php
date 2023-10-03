<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoCambioYuanStoreRequest;
use App\Http\Requests\TipoCambioYuanUpdateRequest;
use App\Http\Resources\TipoCambioYuanCollection;
use App\Models\TipoCambioYuan;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;


class TipoCambioYuanController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-tipocambioyuan'])->only('index');
        //$this->middleware(['auth', 'permission:crear-tipocambioyuan'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-tipocambioyuan'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('TipoCambioYuan/Index', [
            'tipo_cambio_yuan' => new TipoCambioYuanCollection(
                TipoCambioYuan::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(TipoCambioYuanStoreRequest $request)
    {

        $cliente = TipoCambioYuan::create($request->all());
        //return Redirect::route('clientes.index');
    }
    public function show($id)
    {
        $cliente = TipoCambioYuan::findOrFail($id);
        return response()->json([
            "tipo_cambio_yuan" => $cliente
        ]);
    }

    public function update(TipoCambioYuanUpdateRequest $request, $id)
    {
        $tipo_cambio_yuan = TipoCambioYuan::findOrFail($id);
        $tipo_cambio_yuan->update($request->all());
    }


    public function destroy($id)
    {
        $tipo_cambio_yuan = TipoCambioYuan::find($id);
        $tipo_cambio_yuan->delete();

    }
}
