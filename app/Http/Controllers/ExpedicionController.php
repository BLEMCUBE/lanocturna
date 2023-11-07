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
use App\Models\Rma;
use App\Models\RmaStock;
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

            $expedidiones= new VentaCollection(
                Venta::where(function ($query) {
                    $query->where('destino',"WEB")
                    ->orWhere('destino',"MERCADOLIBRE")
                    ->orWhere('destino',"SALON");
                })->where(function ($query) {
                    $query->where('estado',"PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado',"FACTURADO")
                    ->orWhere('estado',"RMA");
                })->get()

                    //whereNot('estado',"COMPLETADO")
                );
        return Inertia::render('Expedicion/Index', [
            'ventas' =>$expedidiones
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
            }])->select('*')
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
            $venta->validador_id =  $validador->id;
            $venta->fecha_validacion=now();
            $venta->save();


            foreach ($request->productos as $producto) {
                $prod = VentaDetalle::find($producto['detalle_id']);
                $prod->update([
                    "producto_validado" =>  $producto['producto_validado']
                ]);
            }

            $rma_json=json_decode($venta->parametro);
            if($venta->tipo=="RMA"){
                $rma=Rma::findOrFail($rma_json->rma->id);
                $rma->modo="ENTREGADO";
                $rma->save();
            }

            if($rma_json!=null){

                if($rma_json->rma->estado="CAMBIO PRODUCTO"){
                    RmaStock::create([
                        'sku' => $rma_json->rma->prod_origen,
                        'cantidad_total' => $rma_json->rma->prod_cantidad,
                        'producto_completo' => $rma_json->opt->producto_completo,
                        'rma_id' => $rma_json->rma->id,
                    ]);
                }
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
