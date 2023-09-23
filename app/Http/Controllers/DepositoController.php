<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarDepositoRequest;
use App\Http\Requests\DepositoImportacionUpdateRequest;
use App\Http\Requests\DepositoStoreRequest;
use App\Http\Requests\DepositoUpdateRequest;
use App\Http\Resources\DepositoCollection;
use App\Http\Resources\DepositoHistorialCollection;
use App\Imports\DepositoImport;
use App\Models\Deposito;
use App\Models\DepositoDetalle;
use App\Models\DepositoHistorial;
use App\Models\DepositoLista;
use App\Models\DepositoProducto;
use App\Models\Producto;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class DepositoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-depositos'])->only('index');
        //$this->middleware(['auth', 'permission:editar-depositos'])->only(['update']);
    }

    public function index()
    {

        $query_depositos = DepositoLista::with(['depositos_productos' => function ($query) {
            $query->select('id', 'sku', 'bultos', 'pcs_bulto', 'deposito_lista_id', 'cantidad_total')
                ->where('bultos', '>', 0)
                ->with(['producto' => function ($query) {
                    $query->select('id', 'origen', 'nombre', 'imagen', 'codigo_barra');
                }]);
        }])->select('id', 'nombre')->orderBy('nombre', 'ASC')->get();


        $depositos = [];
        $det_producto = [];

        foreach ($query_depositos as $deposito) {

            foreach ($deposito->depositos_productos as $prod) {
                array_push($det_producto, [
                    "id" => $prod->id,
                    "sku" => $prod->sku,
                    "bultos" => $prod->bultos,
                    "pcs_bulto" => $prod->pcs_bulto,
                    "cantidad_total" => $prod->cantidad_total,
                    "producto_id" => $prod->producto->id,
                    "nombre" => $prod->producto->nombre,
                    "imagen" => $prod->producto->imagen,
                    "codigo_barra" => $prod->producto->codigo_barra,
                ]);
            }
            array_push($depositos, [
                "id" => $deposito->id,
                "nombre" => $deposito->nombre,
                "productos" => $det_producto,

            ]);
            $det_producto = [];
        }
        return Inertia::render('Deposito/Index', [
            'depositos' => $depositos
        ]);
    }


    public function bultos()
    {
        return Inertia::render('Deposito/Bultos', [
            'productos' => new DepositoCollection(
                Deposito::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function create()
    {
        return Inertia::render('Deposito/Create');
    }

    public function historial()
    {

        return Inertia::render('Deposito/Historial', [
            'historial' => new DepositoHistorialCollection(
                DepositoHistorial::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(DepositoStoreRequest $request)
    {

        $usuario = auth()->user();
        $file = $request->file('archivo');
        DB::beginTransaction();
        try {

            //creando deposito
            $deposito = Deposito::create([
                'nro_carpeta' => $request->nro_carpeta ?? '',
                'nro_contenedor' => $request->nro_contenedor ?? '',
                'estado' => $request->estado ?? '',
                'total' => $request->total ?? 0,
                'fecha_arribado' => $request->fecha_arribado ?? '',
                'fecha_camino' => $request->fecha_camino ?? '',
                'mueve_stock' => $request->mueve_stock ?? false,
                'user_id' => $usuario->id

            ]);

            //importando excel
            Excel::import(new DepositoImport($deposito->id, $deposito->estado), $file);


            DB::commit();
            return Redirect::route('depositos.bultos')->with([
                // 'success' =>  $venta->codigo
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function showModal($id)
    {

        $deposito = Deposito::select(DB::raw("*,DATE_FORMAT(created_at,'%d/%m/%y %H:%i:%s') AS fecha,
            FORMAT(total, 2, 'en_US') as total"))
            ->where('id', $id)->first();
        $importacion = Deposito::find($id);

        $detalle_deposito = DepositoDetalle::with(['producto' => function ($query) {
            $query->select('id', 'nombre', 'codigo_barra', 'origen');
        }])->select('*')->where('deposito_id', $id)->get();

        $lista_productos_movidos = [];

        if ($importacion->estado == 'Arribado') {
            foreach ($detalle_deposito as $producto) {
                $datoProducto = DepositoProducto::where('sku', $producto->sku)->where('deposito_lista_id', 1)->first();
                if (!empty($datoProducto) || !is_null($datoProducto)) {

                    if ($producto->bultos > $datoProducto->bultos) {
                        $mover = ($producto->bultos >= $datoProducto->bultos) ? $producto->bultos - $datoProducto->bultos : $producto->bultos;
                        array_push($lista_productos_movidos, [
                            "sku" => $datoProducto->sku,
                            "bultos_deposito" => $datoProducto->bultos,
                            "bultos_importado" => $producto->bultos,
                            "mover" => $mover,
                            "producto" => $producto->producto->nombre,
                        ]);
                    }
                }
            }
        }

        if (count($lista_productos_movidos) > 0) {
            return response()->json([
                "status" => false,
                "faltantes" => $lista_productos_movidos
            ]);
        } else {
            return response()->json([
                "status" => true,
                "deposito" => $deposito
            ]);
        }
    }
    public function showProductoModal($id)
    {

        $importacion_detalle = DepositoDetalle::with(['deposito' => function ($query) {
            $query->select('*');
        }])->with(['producto' => function ($query) {
            $query->select('id', 'nombre', 'codigo_barra', 'origen');
        }])->where('id', $id)->first();
        return response()->json([
            "importacion_detalle" => $importacion_detalle
        ]);
    }
    public function showCambiarProducto($id)
    {

        $deposito_detalle = DepositoProducto::with(['deposito_lista' => function ($query) {
            $query->select('id', 'nombre');
        }])->with(['producto' => function ($query) {
            $query->select('id', 'origen', 'nombre');
        }])->select('id', 'sku', 'bultos', 'pcs_bulto', 'cantidad_total', 'deposito_lista_id')->where('id', $id)->first();


        //Lista deposito_detalle
        $lista_depo = DepositoLista::whereNot('id', $deposito_detalle->deposito_lista_id)->get();

        $lista_depositos = [];
        foreach ($lista_depo as $destino) {
            array_push($lista_depositos, [
                'code' => $destino->id,
                'name' =>  $destino->nombre,
            ]);
        }
        return response()->json([
            "lista_depositos" => $lista_depositos,
            "deposito_detalle" => $deposito_detalle,
        ]);
    }
    public function show($id)
    {
        $deposito = DB::table('depositos as impor')
            ->select(DB::raw("impor.*,DATE_FORMAT(impor.created_at,'%d/%m/%y %H:%i:%s') AS fecha,
    FORMAT(impor.total, 2, 'en_US') as total"))
            ->where('id', $id)->first();

        $deposito_detalle = DB::table('depositos_detalles as det')
            ->join('productos as prod', 'prod.origen', '=', 'det.sku')
            ->select(DB::raw("det.*,prod.nombre,prod.aduana,prod.imagen,prod.id as producto_id"))
            ->where('deposito_id', $id)->get();

        return Inertia::render('Deposito/Show', [
            'deposito' => $deposito,
            'deposito_detalle' => $deposito_detalle,
        ]);
    }

    public function update(DepositoUpdateRequest $request, $id)
    {
        $importacion = Deposito::find($id);
        $estado = $request->estado;
        $usuario = auth()->user();
        DB::beginTransaction();
        try {

            if ($estado != $importacion->estado) {
                if ($estado == 'Arribado') {

                    //moviendo a Deposito
                    foreach ($importacion->depositos_detalles as $detalle) {
                        $codigo = $detalle->sku;
                        $producto = DepositoProducto::where('sku', '=', $codigo)
                            ->where('deposito_lista_id', '=', 1)->first();
                        if (empty($producto) || is_null($producto)) {
                            //creando registro deposito
                            $nuevo = [
                                "sku" => $detalle->sku,
                                "unidad" => $detalle->unidad,
                                "pcs_bulto" => $detalle->pcs_bulto,
                                "bultos" => $detalle->bultos,
                                "cantidad_total" => $detalle->bultos * $detalle->pcs_bulto,
                                "codigo_barra" => $detalle->codigo_barra,
                                "deposito_lista_id" => 1
                            ];
                            DepositoProducto::create($nuevo);
                        } else {

                            $nuevo_bulto = $producto->bultos + $detalle->bultos;
                            $producto->update([
                                "bultos" => $nuevo_bulto,
                                "cantidad_total" => $nuevo_bulto * $detalle->pcs_bulto,
                            ]);
                        }
                    }
                }

                if ($estado == 'En camino') {

                    //moviendo a Deposito
                    foreach ($importacion->depositos_detalles as $detalle) {
                        $codigo = $detalle->sku;
                        $producto = DepositoProducto::where('sku', '=', $codigo)
                            ->where('deposito_lista_id', '=', 1)->first();
                        if (!empty($producto) || !is_null($producto)) {
                            $nuevo_bulto = $producto->bultos - $detalle->bultos;
                            $producto->update([
                                "bultos" => $nuevo_bulto,
                                "cantidad_total" => $nuevo_bulto * $detalle->pcs_bulto,
                            ]);
                        }
                    }
                }
            }


            $importacion->nro_carpeta = $request->nro_carpeta ?? '';
            $importacion->nro_contenedor = $request->nro_contenedor ?? '';
            $importacion->estado = $request->estado ?? '';
            $importacion->total = $request->total ?? 0;
            $importacion->fecha_arribado = $request->fecha_arribado ?? '';
            $importacion->fecha_camino = $request->fecha_camino ?? '';
            $importacion->mueve_stock = $request->mueve_stock ?? false;
            $importacion->user_id = $usuario->id;
            $importacion->save();


            DB::commit();
            //return Redirect::route('depositos.bultos')->with([
            // 'success' =>  $venta->codigo
            //]);
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function updateProducto(DepositoImportacionUpdateRequest $request, $id)
    {
        $deposito_detalle = DepositoDetalle::find($id);

        //Guardando producto depositodetalle
         $deposito_detalle->update([
            "unidad"=>$request->unidad,
            "bultos"=>$request->bultos,
            "pcs_bulto"=>$request->pcs_bulto,
            "cantidad_total"=>$request->cantidad_total,
        ]);


        return Redirect::back();
    }
    public function updateDeposito(CambiarDepositoRequest $request, $id)
    {

        //buscando producto en deposito

        $datosOrigen = DepositoProducto::where('sku', $request->sku)->where('deposito_lista_id', $request->origen_id)->first();
        $datosDestino = DepositoProducto::where('sku', $request->sku)->where('deposito_lista_id', $request->destino_id)->first();

        DB::beginTransaction();
        try {

            $ne_pcs_bulto = $datosOrigen->pcs_bulto;
            $ne_bultos = $datosOrigen->bultos - $request->bultos;
            $da_producto = Producto::where('origen', '=', $request->sku)->first();
            $da_origen = DepositoLista::where('id', '=', $request->origen_id)->first();
            $da_destino = DepositoLista::where('id', '=', $request->destino_id)->first();
            $usuario = auth()->user();
            $datos_historial = [
                "sku" => $request->sku,
                "producto" => $da_producto->nombre,
                "bultos" => $request->bultos,
                "origen" => $da_origen->nombre,
                "destino" => $da_destino->nombre,
                "usuario" => $usuario->name,
            ];

            //return $datos_historial;

            if (empty($datosDestino) || is_null($datosDestino)) {

                //quitando bultos del deposito de origen.
                $datosOrigen->update([
                    "bultos" => $ne_bultos,
                    "cantidad_total" => $ne_bultos * $ne_pcs_bulto,
                ]);
                //creando registro deposito
                $nuevo = [
                    "sku" => $datosOrigen->sku,
                    "unidad" => $datosOrigen->unidad,
                    "pcs_bulto" => $datosOrigen->pcs_bulto,
                    "bultos" => $request->bultos,
                    "cantidad_total" => $request->bultos * $datosOrigen->pcs_bulto,
                    "codigo_barra" => $datosOrigen->codigo_barra,
                    "deposito_lista_id" => $request->destino_id
                ];
                DepositoProducto::create($nuevo);
            } else {
                $datosOrigen->update([
                    "bultos" => $ne_bultos,
                    "cantidad_total" => $ne_bultos * $ne_pcs_bulto,
                ]);
                $nuevo_bulto = $datosDestino->bultos + $request->bultos;
                $datosDestino->update([
                    "bultos" => $nuevo_bulto,
                    "cantidad_total" => $nuevo_bulto * $datosOrigen->pcs_bulto,
                ]);
            }
            DepositoHistorial::create([
                "datos" => json_encode($datos_historial),
            ]);
            //guardando en tabla deposito historial

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function destroy($id)
    {
        $importacion = Deposito::find($id);
        $estado = $importacion->estado;

        if ($estado == 'Arribado') {
        }
        if ($estado == 'En camino') {
        }

        $importacion->depositos_detalles()->delete();
        $importacion->delete();
    }
}
