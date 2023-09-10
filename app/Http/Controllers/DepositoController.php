<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarDepositoRequest;
use App\Http\Requests\DepositoStoreRequest;
use App\Http\Requests\DepositoUpdateRequest;
use App\Http\Resources\DepositoCollection;
use App\Models\Deposito;
use App\Models\DepositoDetalle;
use App\Models\Producto;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\DB;

class DepositoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-depositos'])->only('index');
        //$this->middleware(['auth', 'permission:crear-depositos'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-depositos'])->only(['update']);
    }

    public function index()
    {

        $depositos = Producto::with(['deposito_detalles' => function ($query) {
            $query->select('id', 'deposito_id', 'importacion_id', 'bultos', 'codigo_barra')
                ->with(['deposito' => function ($query) {
                    $query->select('id', 'nombre', 'descripcion');
                }])
                ->with(['importacion' => function ($query) {
                    $query->select('id', 'nro_carpeta', 'nro_contenedor', 'estado');
                }]);
        }])->select('id', 'origen', 'nombre', 'imagen', 'codigo_barra')
            //->where('id', '1430')
            ->get();



        //return $depositos;

        return Inertia::render('Deposito/Deposito', [
            'depositos' => $depositos
        ]);
    }

    public function nombres()
    {

        return Inertia::render('Deposito/Index', [
            'tipo_cambio' => new DepositoCollection(
                Deposito::orderBy('id', 'ASC')
                    ->get()
            )
        ]);
    }

    public function store(DepositoStoreRequest $request)
    {

        $cliente = Deposito::create($request->all());
    }

    public function show($id)
    {
        $deposito = DepositoDetalle::with(['deposito' => function ($query) {
            $query->select('id', 'nombre', 'descripcion');
        }])->findOrFail($id);

        $lis_deposito = Deposito::whereNot('id', $deposito->deposito_id)->get();
        $lista_depositos = [];
        foreach ($lis_deposito as $depo) {
            array_push($lista_depositos, [
                'code' => $depo->id,
                'name' =>  $depo->nombre,
            ]);
        }

        //return $lista_depositos;
        return response()->json([
            "deposito" => $deposito,
            "lista_depositos" => $lista_depositos
        ]);
    }

    public function update(DepositoUpdateRequest $request, $id)
    {
        $tipo_cambio = Deposito::findOrFail($id);
        $tipo_cambio->update($request->all());
    }


    public function updateDeposito(CambiarDepositoRequest $request, $id)
    {
        $detalle = DepositoDetalle::find($id);

        $existeDeposito=DepositoDetalle::where('codigo_barra',$detalle->codigo_barra)->where('deposito_id',$request->destino_id)->first();
        $datosOrigen=DepositoDetalle::where('codigo_barra',$detalle->codigo_barra)->where('deposito_id',$request->origen_id)->first();


        //return !is_null($existeDeposito);
        DB::beginTransaction();
        try {
            if(!is_null($existeDeposito)){
                //return 'existe';
                $existeDeposito->update([
                    'bultos'=>$existeDeposito->bultos + $request->bultos
                ]);

                $datosOrigen->update([
                    'bultos'=>$detalle->bultos - $request->bultos
                ]);

            }else{
                //return 'no existe';
                DepositoDetalle::create([
                    "bultos" => $request->bultos,
                    "importacion_id" => $detalle->importacion_id,
                    "deposito_id" => $request->destino_id,
                    "codigo_barra" => $detalle->codigo_barra
                ]);

                $datosOrigen->update([
                    'bultos'=>$detalle->bultos - $request->bultos
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


    public function destroy($id)
    {
    }
}
