<?php

namespace App\Http\Controllers;

use App\Http\Resources\VentaCollection;
use Exception;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VentaResource;
use App\Models\Configuracion;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;

class ExpedicionController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-expediciones'])->only('index');
    }

    public function index()
    {


        return Inertia::render('Expedicion/Index', [
            'ventas' => new VentaCollection(
                Venta::where('destino',"WEB")
                ->orWhere('destino',"SALON")
                ->where('estado',"PENDIENTE DE FACTURACIÓN")
                ->orWhere('estado',"FACTURADO")
                ->orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }

    public function show($id)
    {
        $subtema = Venta::with(['detalles_ventas' => function ($query) {
            $query->select('venta_detalles.*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])
            ->with(['vendedor' => function ($query) {
                $query->select('users.id', 'users.name');
            }])
            ->orderBy('id', 'DESC')->findOrFail($id);

        $venta = new VentaResource($subtema);
        return Inertia::render('Expedicion/Show', [
            'venta' => $venta
        ]);
    }

    public function verificarCodigoMaestro(Request $request){
        $codigo=Configuracion::where('slug','codigo-maestro')->first();
        $validated = $request->validate([
            'codigo' => 'required',
        ]);

         if (Hash::check( $request->codigo, $codigo->value)) {
            //dd( "Password matching");
         } else {
            //dd( "Password is not matching");
            throw ValidationException::withMessages([
                'codigo' => __('Código maestro inválido'),
            ]);
         }
      }


    public function validarProductos(Request $request, $id)
    {


        $venta = Venta::findOrFail($id);

        $validador = auth()->user();
        //return $request;

        DB::beginTransaction();
        try {
            $venta->validado =true;
            $venta->estado="COMPLETADO";
            //$venta->validador_id =  $validador->id;
            $venta->save();

            //actualizando stock producto
            /*foreach ($venta->detalles_ventas as $producto) {
                $prod = Producto::find($producto['producto_id']);
                $old_stock = $prod->stock;
                $new_stock = $old_stock - $producto['cantidad'];
                $prod->update([
                    "stock" => $new_stock
                ]);
            }*/

            foreach ($request->productos as $producto) {
                $prod = VentaDetalle::find($producto['detalle_id']);
                $prod->update([
                    "producto_validado" =>  $producto['producto_validado']
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
