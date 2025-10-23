<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtributoStoreRequest;
use App\Models\Atributo;
use App\Models\RespuestaRapida;
use App\Models\Configuracion;
use App\Services\ConfiguracionService;

class RespuestaRapidaController extends Controller
{
	public function __construct(
		private ConfiguracionService $configuracionService
	) {}

	public function index()
	{
		$configuracion = Configuracion::get();
		$datos =	RespuestaRapida::select('id', 'titulo', 'descripcion', 'color')->orderBy('titulo', 'ASC')->get();
		$saludo = $this->configuracionService->getOp($configuracion, 'pregunta-saludo');
		$firma = $this->configuracionService->getOp($configuracion, 'pregunta-firma');
		return response()->json([
			"respuestas" => $datos,
			"firma" => $firma,
			"saludo" => $saludo
		]);
	}

	public function store(AtributoStoreRequest $request)
	{
		Atributo::create($request->only('nombre'));
	}
}
