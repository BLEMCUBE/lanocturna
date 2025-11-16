<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\MensajeCollection;
use App\Models\MercadoLibreMensaje;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MensajesController extends Controller
{

	public function index()
	{
		$datos = MercadoLibreMensaje::withVenta()
			->where('is_from_seller', '=', 0)
			->orderByDesc('date_created')
			->get()
			->unique('pack_id')
			->values()
			->map(function ($mensaje) {
				$mensaje->venta = $mensaje->ventaPorPack ?? $mensaje->ventaPorId;
				return $mensaje;
			});
		$datosFinal = new MensajeCollection($datos);
		return Inertia::render('MercadoLibre/Mensajes', [
			'datos' => $datosFinal,
		]);
	}

	public function sinLeer()
	{
		$datos = MercadoLibreMensaje::withVenta()
			->orderByDesc('date_created')
			->where('is_from_seller', '=', 0)
			->where('is_read', '=', 0)
			->get()
			->unique('pack_id')
			->values()
			->map(function ($mensaje) {
				$mensaje->venta = $mensaje->ventaPorPack ?? $mensaje->ventaPorId;
				return $mensaje;
			});
		$datosFinal = new MensajeCollection($datos);
		return Inertia::render('MercadoLibre/MensajesSinLeer', [
			'datos' => $datosFinal,
		]);
	}
}
