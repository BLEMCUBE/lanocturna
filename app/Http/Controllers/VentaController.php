<?php

namespace App\Http\Controllers;

use App\Events\PedidoEvent;
use App\Http\Requests\VentaStoreRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\VentaCollection;
use App\Models\Cliente;
use App\Models\Configuracion;
use Exception;
use App\Models\MetodoPago;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Venta;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Helpers\HelperNumeros;
use App\Http\Requests\VentaUpdateRequest;
use App\Models\Cotizacion;
use App\Models\Destino;
use App\Models\Envio;
use App\Models\Kardex;
use App\Models\Pago;
use App\Models\TipoCambio;
use App\Models\User;
use App\Notifications\PedidoNotification;

class VentaController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-ventas'])->only('index');
        //$this->middleware(['auth', 'permission:crear-ventas'])->only(['store','create']);
        //$this->middleware(['auth', 'permission:editar-ventas'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-ventas'])->only(['destroy']);
    }

    public function index()
    {

        $ultimo_tipo_cambio=TipoCambio::all()->last();

        $hoy_tipo_cambio=false;

        $actual=Carbon::now()->format('Y-m-d');
        if(!empty($ultimo_tipo_cambio)){
            $fecha=Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
        if($fecha->eq($actual)){
            $hoy_tipo_cambio=true;
            $tipo_cambio=$ultimo_tipo_cambio;
            }else{
                $hoy_tipo_cambio=false;
            }
        }

        return Inertia::render('Venta/Index', [
            'tipo_cambio'=>$hoy_tipo_cambio,
            'ventas' => new VentaCollection(
                Venta::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }
    public function create()
    {
        $ultimo_tipo_cambio=TipoCambio::all()->last();

        $hoy_tipo_cambio=false;

        $actual=Carbon::now()->format('Y-m-d');
        if(!empty($ultimo_tipo_cambio)){
            $fecha=Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
        if($fecha->eq($actual)){
            $hoy_tipo_cambio=true;
            $tipo_cambio= number_format($ultimo_tipo_cambio->valor,2)??'' ;
            }else{
                $hoy_tipo_cambio=false;
            }
        }


        $last = Venta::latest()->first();
        $vendedor = auth()->user();

        if (empty($last) || is_null($last)) {
            $codigo = zero_fill(1, 8);
        } else {
            $codigo = zero_fill($last->codigo + 1, 8);

        }

        //Lista cliente
        $lista_clientes = Cliente::get();
        $lista_cliente = Cliente::select('id', 'nombre')->get();

        $clientes = [];
        foreach ($lista_clientes as $cliente) {
            array_push($clientes, [
                'value' => $cliente->id,
                'label' => $cliente->nombre,
            ]);
        }
        //Lista destino
        $lista_destin = Destino::get();

        $lista_destinos = [];
        foreach ($lista_destin as $destino) {
            array_push($lista_destinos, [
                'value' => $destino->nombre,
                'label' =>  $destino->nombre,
            ]);
        }

        return Inertia::render('Venta/Create', [
            'hoy_tipo_cambio'=>$hoy_tipo_cambio,
            'tipo_cambio'=>$tipo_cambio,
            'codigo' => $codigo,
            'user_id' => $vendedor->id,
            'vendedor' => $vendedor->name,
            'clientes' => $clientes,
            'lista_clientes' => $lista_cliente,
            'lista_destinos' => $lista_destinos,
            'productos' => new ProductoVentaCollection(
                Producto::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(VentaStoreRequest $request)
    {
        $last = Venta::latest()->first();
        $vendedor = auth()->user();

        if (empty($last) || is_null($last)) {
            $codigo = zero_fill(1, 8);
        } else {
            $codigo = zero_fill($last->codigo + 1, 8);

        }

        DB::beginTransaction();
        try {

            //creando venta
            $venta = Venta::create([
                'codigo' => $codigo,
                'total_sin_iva' => $request->total_sin_iva ?? 0,
                'total' => $request->total ?? 0,
                'estado' => $request->estado,
                'moneda' => $request->moneda,
                'tipo_cambio' => $request->tipo_cambio,
                'destino' => $request->destino,
                'cliente' => json_encode($request->cliente),
                'observaciones' => $request->observaciones,
                'vendedor_id' => $vendedor->id,

            ]);

            //creando detalle venta
            foreach ($request->productos as $producto) {

                $venta->detalles_ventas()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "precio" => $producto['precio'],
                        "precio_sin_iva" => $producto['precio_sin_iva'],
                        "cantidad" => $producto['cantidad'],
                        "total" => $producto['total'],
                        "total_sin_iva" => $producto['total_sin_iva'],
                    ]
                );
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
