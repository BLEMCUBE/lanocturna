<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ProductoCollection;
use App\Http\Resources\ProductoResource;

use App\Models\Producto;

use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;


class ProductoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        $this->middleware(['auth', 'permission:lista-productos'])->only('index');
        $this->middleware(['auth', 'permission:crear-productos'])->only(['store']);
        $this->middleware(['auth', 'permission:editar-productos'])->only(['update']);
        $this->middleware(['auth', 'permission:eliminar-productos'])->only(['destroy']);
    }

    public function index()
    {
        return Inertia::render('Producto/Index', [
            'productos' => new ProductoCollection(
                Producto::orderBy('id', 'ASC')
                    ->get()
            )
        ]);
    }

    public function create()
    {
        return Inertia::render('Producto/Create');
    }

    public function store(ProductoStoreRequest $request)
    {
        $producto = Producto::create($request->input());
        //imagen
        if ($request->hasFile('photo')) {
            sleep(1);
            $fileName = time() . '.' . $request->photo->extension();
            $producto->update([
                'imagen' => "/images/productos/" . $fileName
            ]);
            $request->photo->move(public_path('images/productos'), $fileName);
        } else {
            $producto->update([
                'imagen' => "/images/productos/sin_foto.png"
            ]);
        }

    }

    public function edit($id){
        $producto = Producto::findOrFail($id);
        return Inertia::render('Producto/Edit', [
            'producto' => $producto
        ]);

    }

    public function update(ProductoUpdateRequest $request,$id){
        $producto = Producto::find($id);
        $old_photo = $producto->imagen;
        $producto->origen = $request->input('origen');
        $producto->nombre = $request->input('nombre');
        $producto->aduana = $request->input('aduana');
        $producto->codigo_barra     = $request->input('codigo_barra');
        $producto->stock = $request->input('stock');
        $producto->stock_minimo = $request->input('stock_minimo');
        $producto->save();

        //imagen
           if ($request->hasFile('photo')) {
            sleep(1);
            $url_save = public_path() . $old_photo;
            $fileName = time() . '.' . $request->photo->extension();
                //eliminar imagen
                if (file_exists($url_save) && $old_photo != "/images/productos/sin_foto.png") {
                    unlink($url_save);
                }
            $producto->update([
                'imagen' => "/images/productos/" . $fileName
            ]);
            $request->photo->move(public_path('images/productos'), $fileName);
        }
    }

    public function show($id)
    {
        $producto = Producto::findOrFail($id);
        return Inertia::render('Producto/Show', [
            'producto' => $producto
        ]);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        $old_photo = $producto->imagen;
        $url_save = public_path() . $old_photo;

        //eliminar imagen si existe
        if (file_exists($url_save) && $old_photo != "/images/productos/sin_foto.png") {
            unlink($url_save);
        }
        $producto->delete();
    }


}
