<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ProductoCollection;
use App\Http\Resources\ProductoResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Categoria;
use App\Models\Color;
use App\Models\Curva;
use App\Models\Diferenciador;
use App\Models\Familia;
use App\Models\Longitud;
use App\Models\Marca;
use App\Models\Peso;
use App\Models\Producto;
use App\Models\SubProducto;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Yajra\Datatables\Datatables;

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

            'productos' =>
                Producto::orderBy('id', 'ASC')
                    ->get()

            /*'productos' => new ProductoCollection(
                Producto::orderBy('id', 'ASC')
                    ->get()
            )*/
        ]);
    }

    public function show($id)
    {
        $cliente = Producto::findOrFail($id);
        return response()->json([
            "cliente" => $cliente
        ]);
    }
    public function paginate()
    {
        $post = Producto::latest()->get();
            return Datatables::of($post)
            /*->addColumn('action', function($post){
                return '';})*/
                   /* ->addColumn('action', function ($post) {
                        $nombre="uno";
                        $showBtn =  '<button ' .
                                        ' class="btn btn-outline-info" ' .
                                        ' onclick="showProject(' . $post->id . ')">Show' .
                                    '</button> ';

                        $editBtn =  '<span '.
                        'class="inline-block rounded bg-red-700 px-2 py-1 text-base font-semibold text-white mr-1 mb-1 hover:bg-red-600"> '.
                        ' <button onclick="eliminar('.$post->id.','.nl2br($nombre).')" > '.
                         '<i class="fas fa-trash-alt"> </i> </button> '.'</span>';

                        $deleteBtn =  '<button ' .
                                        ' class="btn btn-outline-danger" ' .
                                        ' onclick="destroyProject(' . $post->id . ')">Delete' .
                                    '</button> ';

                        return $showBtn .  $deleteBtn;
                    })*/
                    /*->rawColumns(
                    [
                        'action',
                    ])*/
                ->rawColumns(['action'])
                //->make(true);
                    ->toJson();

        /*$datos=Producto::orderBy('id', 'ASC')
        ->paginate(10)
        ->appends(Request::all());
        return response()->json([
            "draw"=>1,
  "recordsTotal"=> $datos->total(),
  "recordsFiltered"=>  $datos->total(),
            "data" =>$datos->items()

        ]);*/


    }
    public function paginateWeb(){
        $post = Producto::latest()->get();
        $datos=[];
        /*foreach ($post as $key=> $valor) {
          array_push($datos,[
            'id'=> $valor->id, 'nombre'=> $valor->nombre, 'aduana'=> $valor->aduana,
            'aduana'=> $valor->aduana,
           ' key'=> $key+1
          ]);
        }*/
        return response()->json([
            /*"headers"=>[
                ['title'=> 'id', 'dataIndex'=> 'id', 'key'=> 'key'],
                ['title'=> 'nombre', 'dataIndex'=> 'nombre', 'key'=> 'nombre'],
                ['title'=> 'aduana', 'dataIndex'=> 'aduana', 'key'=> 'aduana'],
                ['title'=> 'Action', 'key'=> 'operation', 'width'=> 100],

            ],*/
            "datos"=>$post

        ]);
    }



}
