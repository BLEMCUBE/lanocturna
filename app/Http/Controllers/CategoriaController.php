<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaStoreRequest;
use App\Http\Requests\CategoriaUpdateRequest;
use App\Http\Resources\CategoriaCollection;
use App\Models\Categoria;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index()
    {
        return Inertia::render('Categoria/Index', [
            'categorias' => new CategoriaCollection(
                Categoria::orderBy('name', 'ASC')
                    ->get()
            )
        ]);
    }
    
    public function show($id)
    {
        $cate = Categoria::findOrFail($id);
        return response()->json([
            "categoria" => $cate
        ]);
    }

    public function store(CategoriaStoreRequest $request)
    {
        Categoria::create($request->all());
    }

    public function update(CategoriaUpdateRequest $request, $id)
    {
        $cate = Categoria::find($id);
        $cate->name = $request->input('name');
        $cate->save();
    }

    public function destroy($id)
    {
        $cate = Categoria::find($id);
        $cate->delete();
    }
}
