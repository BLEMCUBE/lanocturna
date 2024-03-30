<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioStoreRequest;
use App\Http\Requests\ImportacionEnvioStoreRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\VentaCollection;
use Exception;
use Carbon\Carbon;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VentaResource;
use App\Imports\MercadoLibreImport;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use App\Models\Configuracion;
use App\Models\Destino;
use App\Models\Producto;
use App\Models\Rma;
use App\Models\RmaStock;
use App\Models\TipoCambio;
use Illuminate\Support\Facades\Redirect;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as Req;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpException;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;


class EnvioController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-envios'])->only('index');
        //$this->middleware(['auth', 'permission:crear-envios'])->only(['store','create']);
        //$this->middleware(['auth', 'permission:editar-envios'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-envios'])->only(['destroy']);
    }

    //lista UES
    public function index()
    {

        $expedidiones = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "UES");
            })->where(function ($query) {
                $query->where('estado', "PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado', "PENDIENTE DE VALIDACIÓN")
                    ->orWhere('estado', "VALIDADO")
                    ->orWhere('estado', "FACTURADO");
            })
                ->select('*')
                ->orderBy('created_at', 'DESC')->get()
        );
        return Inertia::render('Envio/Index', [
            'ventas' => $expedidiones
        ]);
    }

    //FLEX
    public function indexFlex()
    {
        $expedidiones = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "FLEX");
            })->where(function ($query) {
                $query->where('estado', "PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado', "PENDIENTE DE VALIDACIÓN")
                    ->orWhere('estado', "VALIDADO")
                    ->orWhere('estado', "FACTURADO");
            })
                ->select('*')
                ->orderBy('created_at', 'DESC')->get()
        );
        return Inertia::render('Envio/IndexFlex', [
            'ventas' => $expedidiones
        ]);
    }

    //DAC

    public function indexDac()
    {
        $expedidiones = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "DAC");
            })->where(function ($query) {
                $query->where('estado', "PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado', "PENDIENTE DE VALIDACIÓN")
                    ->orWhere('estado', "VALIDADO")
                    ->orWhere('estado', "FACTURADO");
            })
                ->select('*')
                ->orderBy('created_at', 'DESC')->get()
        );
        return Inertia::render('Envio/IndexDac', [
            'ventas' => $expedidiones
        ]);
    }

    //CADETERIA 
    public function indexCadeteria()
    {
        $expedidiones = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA");
            })->where(function ($query) {
                $query->where('estado', "PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado', "PENDIENTE DE VALIDACIÓN")
                    ->orWhere('estado', "VALIDADO")
                    ->orWhere('estado', "FACTURADO");
            })
                ->select('*')
                ->orderBy('created_at', 'DESC')->get()

        );
        return Inertia::render('Envio/IndexCadeteria', [
            'ventas' => $expedidiones
        ]);
    }
    public function create()
    {


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
        $productoLista = Producto::with(['importacion_detalles' => function ($query) {
            $query->select('id', 'sku', 'cantidad_total', 'importacion_id', 'estado');
        }, 'importacion_detalles.importacion' => function ($query1) {
            $query1->select('id', 'estado', 'nro_carpeta');
        }])->select('*')
            ->orderBy('nombre', 'ASC')

            ->get();
        $resultadoProductoLista = new ProductoVentaCollection($productoLista);
        return Inertia::render('Envio/Create', [
            'codigo' => $codigo,
            'user_id' => $vendedor->id,
            'vendedor' => $vendedor->name,
            'clientes' => $clientes,
            'lista_clientes' => $lista_cliente,
            'lista_destinos' => $lista_destinos,
            'productos' => $resultadoProductoLista
        ]);
    }

    public function store(EnvioStoreRequest $request)
    {
        $vendedor = auth()->user();

        DB::beginTransaction();
        try {

            //creando venta
            $venta = Venta::create([
                'nro_compra' => $request->nro_compra ?? '',
                'estado' => $request->estado,
                'destino' => $request->destino,
                'moneda' => $request->moneda,
                'tipo' => $request->tipo ?? 'ENVIO',
                'vendedor_id' => $vendedor->id,
                'facturador_id' => $vendedor->id,
                'fecha_facturacion' => now()
            ]);
            $venta->update([
                "codigo" => zero_fill($venta->id, 8)
            ]);

            //creando detalle venta
            foreach ($request->productos as $producto) {

                $venta->detalles_ventas()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "cantidad" => $producto['cantidad'],

                    ]
                );
            }

            //actualizando stock producto
            foreach ($request->productos as $produ) {
                $prod = Producto::find($produ['producto_id']);
                $old_stock = $prod->stock;
                $new_stock = $old_stock - $produ['cantidad'];
                $prod->update([
                    "stock" => $new_stock,
                    "stock_futuro" => $new_stock + $prod->en_camino
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
    public function historialEnvios()
    {

        $ultimo_tipo_cambio = TipoCambio::all()->last();

        $hoy_tipo_cambio = false;

        $actual = Carbon::now()->format('Y-m-d');
        if (!empty($ultimo_tipo_cambio)) {
            $fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
            if ($fecha->eq($actual)) {
                $hoy_tipo_cambio = true;
                $tipo_cambio = $ultimo_tipo_cambio;
            } else {
                $hoy_tipo_cambio = false;
            }
        }

        $venta_query = new VentaCollection(
            Venta::select('*')->where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "DAC");
            })
                ->when(Req::input('inicio'), function ($query) {
                    $query->whereDate('created_at', '>=', Req::input('inicio') . ' 00:00:00');
                })
                ->when(Req::input('fin'), function ($query) {
                    $query->whereDate('created_at', '<=', Req::input('fin') . ' 23:59:00');
                })
                ->where(function ($query) {
                    $query->where("tipo", "=", "VENTA")
                        ->orWhere("tipo", "=", "ENVIO");
                })
                ->where('estado', 'COMPLETADO')->orderBy('id', 'DESC')->get()
        );
        return Inertia::render('Envio/Historial', [
            'tipo_cambio' => $hoy_tipo_cambio,
            'ventas' => new VentaCollection(
                $venta_query
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
        return Inertia::render('Envio/Show', [
            'venta' => $venta
        ]);
    }

    public function verificarCodigoMaestro(Request $request)
    {
        $codigo = Configuracion::where('slug', 'codigo-maestro')->first();
        $validated = $request->validate([
            'codigo' => 'required',
        ]);

        if (Hash::check($request->codigo, $codigo->value)) {
        } else {
            throw ValidationException::withMessages([
                'codigo' => __('Código maestro inválido'),
            ]);
        }
    }


    public function detalle($id)
    {
        $venta_query = Venta::with(['detalles_ventas' => function ($query) {
            $query->select('venta_detalles.*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])
            ->with(['vendedor' => function ($query) {
                $query->select('users.id', 'users.name');
            }])
            ->with(['facturador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['validador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('id', 'DESC')->findOrFail($id);
        //return $venta_query;
        $venta = new VentaResource($venta_query);
        return Inertia::render('Envio/Detalle', [
            'venta' => $venta
        ]);
    }

    public function validarProductos(Request $request, $id)
    {

        $venta = Venta::findOrFail($id);

        $validador = auth()->user();

        DB::beginTransaction();
        try {
            $venta->validado = true;
            $venta->estado = "COMPLETADO";
            $venta->validador_id =  $validador->id;
            $venta->fecha_validacion = now();
            $venta->save();

            foreach ($request->productos as $producto) {
                $prod = VentaDetalle::find($producto['detalle_id']);
                $prod->update([
                    "producto_validado" =>  $producto['producto_validado']
                ]);
            }
            //actualizar rma a entregado
            $rma_json = json_decode($venta->parametro);
            if ($venta->tipo == "RMA") {
                $rma = Rma::findOrFail($rma_json->rma->id);
                $rma->modo = "ENTREGADO";
                $rma->save();
            }

            if ($rma_json != null) {

                if ($rma_json->rma->estado = "CAMBIO PRODUCTO") {
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

    public function generarTicket($id)
    {

        $venta = Venta::where("id", "=", $id)->get();



        if (!empty($venta)) {
            $customPaper = array(0, 0, 226.77, 283.46);
            $cliente = json_decode($venta[0]->cliente);
            $data = [
                'codigo' => $venta[0]->codigo,
                'nro_compra' => is_null($venta[0]->nro_compra) ? $venta[0]->codigo : $venta[0]->nro_compra,
                'cliente' => $cliente->nombre ?? '',
                'localidad' => $cliente->localidad ?? '',
                'direccion' => $cliente->direccion ?? '',
                'telefono' => $cliente->telefono ?? '',
                'fecha' => (now())->format('d/m/Y H:i:s')
            ];

            $pdf = Pdf::loadView('pdfs.ticketEnvio', ['data' => $data]);
            $pdf->setPaper($customPaper);
            return $pdf->stream('ticket_' . $data['codigo'] . '.pdf');
        } else {
            return Redirect::route('envios.index');
        }
    }

    public function uploadExcel(ImportacionEnvioStoreRequest $request)
    {
        $usuario = auth()->user();
        $file = $request->file('archivo');

        $no_existe = [];
        $existe_compra = [];
        $existe_stock = [];
        $filas = Excel::toArray([], $file);
        $filas_a = array_slice($filas[0], 1);

        $n_fila = 1;
        foreach ($filas_a as $fila) {
            if (!empty($fila[0])) {

                $prod = Producto::where('origen', '=', $fila[14])->first();
                $compra = Venta::where('nro_compra', '=', $fila[0])
                    ->whereNull('fecha_anulacion')->first();
                $n_fila = $n_fila + 1;

                if (is_null($prod)) {
                    array_push($no_existe, [
                        'fila' => $n_fila,
                        'sku' => $fila[14],
                    ]);
                }


                if (!empty($prod)) {
                    if ($prod->stock < $fila[5]) {
                        array_push($existe_stock, [
                            'fila' => $n_fila,
                            'sku' => $fila[14],
                            'stock' => $prod->stock
                        ]);
                    }
                }

                //if (!empty($fila[0])) {
                if (!empty($compra)) {
                    array_push($existe_compra, [
                        'fila' => $n_fila,
                        'nro_compra' => $fila[0],
                    ]);
                }
                //}
            }
        }

        //if (count($no_existe) > 1  || count($existe_compra) > 0 || count($existe_stock) > 1) {
        if (count($no_existe) > 0  || count($existe_stock) > 1) {
            throw ValidationException::withMessages([
                'filas' => [$no_existe],
                'compras' => [$existe_compra],
                'stock' => [$existe_stock],
            ]);
        } else {

            DB::beginTransaction();
            try {

                //importando excel
                Excel::import(new MercadoLibreImport($usuario, $request->destino), $file);

                DB::commit();
                //return Redirect::route('envios.index')->with([]);
            } catch (Exception $e) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }
}
