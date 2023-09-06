<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioStoreRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\VentaCollection;
use Exception;
use Carbon\Carbon;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VentaResource;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use App\Models\Configuracion;
use App\Models\Destino;
use App\Models\Producto;
use App\Models\TipoCambio;
use Illuminate\Support\Facades\Redirect;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Elibyy\TCPDF\Facades\TCPDF;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function index()
    {

        $expedidiones = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "MERCADOLIBRE")
                    ->orWhere('destino', "DAC");
            })->where(function ($query) {
                $query->where('estado', "PENDIENTE DE FACTURACIÓN")
                    ->orWhere('estado', "FACTURADO");
            })->orderBy('id', 'DESC')->get()
        );
        return Inertia::render('Envio/Index', [
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

        return Inertia::render('Envio/Create', [
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

    public function store(EnvioStoreRequest $request)
    {
        $vendedor = auth()->user();

        DB::beginTransaction();
        try {

            //creando venta
            $venta = Venta::create([
                'nro_compra' => $request->total ?? 0,
                'estado' => $request->estado,
                'destino' => $request->destino,
                'tipo' => $request->tipo,
                'vendedor_id' => $vendedor->id,
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
                    "stock" => $new_stock
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

        $venta_query = Venta::orderBy('created_at', 'DESC')
            ->get();
        $venta_query = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "MERCADOLIBRE")
                    ->orWhere('destino', "DAC");
            })->orderBy('id', 'DESC')->get()
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
            //dd( "Password matching");
        } else {
            //dd( "Password is not matching");
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
            $venta->save();

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
}
