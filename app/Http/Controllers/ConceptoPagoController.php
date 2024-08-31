<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConceptoPagoStoreRequest;
use App\Http\Requests\ConceptoPagoUpdateRequest;
use App\Http\Resources\ConceptoPagoCollection;
use App\Models\ConceptoPago;
use Inertia\Inertia;


class ConceptoPagoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-conceptopago'])->only('index');
        //$this->middleware(['auth', 'permission:crear-conceptopago'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-conceptopago'])->only(['update']);
    }

    public function index()
    {

        return Inertia::render('ConceptoPago/Index', [
            'items' => new ConceptoPagoCollection(
                ConceptoPago::orderBy('nombre', 'ASC')
                    ->get()
            )
        ]);
    }

    public function store(ConceptoPagoStoreRequest $request)
    {
        $item = ConceptoPago::create($request->all());
    }

    public function show($id)
    {
        $item = ConceptoPago::findOrFail($id);
        return response()->json([
            "item" => $item
        ]);
    }

    public function update(ConceptoPagoUpdateRequest $request, $id)
    {
        $item = ConceptoPago::findOrFail($id);
        $item->update($request->all());
    }


    public function destroy($id)
    {
        $item = ConceptoPago::find($id);
        $item->delete();

    }
}
