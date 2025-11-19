<?php

namespace App\Http\Controllers;

use App\Http\Requests\RespuestaRapidaRequest;
use App\Models\RespuestaRapida;
use App\Models\Configuracion;

class RespuestaRapidaController extends Controller
{

	public function index($tipo)
	{
		$datos =	RespuestaRapida::select('id', 'titulo', 'descripcion', 'color','tipo')
		->where('tipo','=',$tipo)->orderBy('titulo', 'ASC')->get();
		$saludo = Configuracion::where('slug', 'pregunta-saludo')->first();
		$firma = Configuracion::where('slug', 'pregunta-firma')->first();

		return response()->json([
			"respuestas" => $datos,
			"firma" => $firma,
			"saludo" => $saludo
		]);
	}

	public function saludoFirma()
	{
		$saludo = Configuracion::where('slug', 'pregunta-saludo')->first();
		$firma = Configuracion::where('slug', 'pregunta-firma')->first();
		return response()->json([
			"firma" => $firma,
			"saludo" => $saludo
		]);
	}

	public function update(RespuestaRapidaRequest $request)
	{
		//actualizar saludo y firma
		$saludo = Configuracion::find($request->saludo[0]['id']);
		$saludo->value = $request->saludo[0]['value'];
		$saludo->save();

		$firma = Configuracion::find($request->firma[0]['id']);
		$firma->value = $request->firma[0]['value'];
		$firma->save();

		//etiquetas
		$etiq = $request->etiquetas;
		foreach ($etiq as $key => $value) {
			if (!is_null($value['id'])) {
				RespuestaRapida::updateOrCreate(
					['id' => $value['id']],
					[
						'titulo' => $value['titulo'] ?? '',
						'descripcion' => $value['descripcion'] ?? '',
						'tipo' => $value['tipo'] ?? '',
						'color' => $value['color'] ?? '',
					]
				);
			} else {
				RespuestaRapida::create([
					'titulo' => $value['titulo'] ?? '',
					'descripcion' => $value['descripcion'] ?? '',
					'tipo' => $value['tipo'] ?? '',
					'color' => $value['color'] ?? '',
				]);
			}
		}
	}

	public function destroy($id)
	{
		$item = RespuestaRapida::find($id);
		$item->delete();
	}
}
