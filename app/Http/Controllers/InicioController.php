<?php

namespace App\Http\Controllers;


use App\Models\Venta;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class InicioController extends Controller
{
    public function index()
    {

        $primerDiaMes = Carbon::now()->startOfMonth()->toDateString();
        $ultimoDiaMes = Carbon::now()->endOfMonth()->toDateString();
        $total_ventas = Venta::select('total')->sum('total');


        //datos grafico ventas
        $consulta_ventas = DB::table('ventas as ve')
            ->select(DB::raw("ve.*, SUM(ve.total) AS total,DATE_FORMAT(ve.created_at,'%d/%m/%y') AS fecha"))
            ->when(Request::input('inicio'), function ($query, $search) {
                $query->whereDate('ve.created_at', '>=', Request::input('inicio'));
            })
            ->when(Request::input('fin'), function ($query, $search) {
                $query->whereDate('ve.created_at', '<=', Request::input('fin'));
            })
            //->whereDate('ve.created_at', '>=', $primerDiaMes)
            //->whereDate('ve.created_at', '<=', $ultimoDiaMes)
            ->where('ve.tipo', '=', 'VENTA')
            ->where('ve.facturado', '=', '1')
            ->orderBy('ve.created_at', 'asc')
            ->groupBy('fecha')
            ->get();


        $datos_grafico_ventas = [];

        foreach ($consulta_ventas as $consulta_venta) {
            array_push(
                $datos_grafico_ventas,
                [
                    "categoria" => $consulta_venta->fecha,
                    "datos" => round(floatval($consulta_venta->total),2),
                ]
            );
        }
        $datos_grafico_ventas = [
            "categoria" => array_column($datos_grafico_ventas, "categoria"),
            "datos" => array_column($datos_grafico_ventas, "datos"),
        ];
        //return $datos_grafico_ventas;
        return Inertia::render('Inicio', [
            'total_ventas' => number_format($total_ventas, 2),
            'datos_grafico_ventas' => $datos_grafico_ventas,
        ]);
    }
}
