<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetodoPagoStoreRequest;
use App\Http\Requests\MetodoPagoUpdateRequest;
use App\Http\Resources\MetodoPagoCollection;
use App\Models\MetodoPago;
use Inertia\Inertia;


class MetodoPagoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-metodopago'])->only('index');
        //$this->middleware(['auth', 'permission:crear-metodopago'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-metodopago'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('MetodoPago/Index', [
            'items' => new MetodoPagoCollection(
                MetodoPago::orderBy('nombre', 'ASC')
                    ->get()
            )
        ]);
    }

    public function store(MetodoPagoStoreRequest $request)
    {
        $item = MetodoPago::create($request->all());
    }

    public function show($id)
    {
        $item = MetodoPago::findOrFail($id);
        return response()->json([
            "item" => $item
        ]);
    }

    public function update(MetodoPagoUpdateRequest $request, $id)
    {
        $item = MetodoPago::findOrFail($id);
        $item->update($request->all());
    }


    public function destroy($id)
    {
        $item = MetodoPago::find($id);
        $item->delete();

    }
}
