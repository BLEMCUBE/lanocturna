<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreguntaCollection;
use App\Http\Resources\PreguntaHistorialCollection;
use App\Models\Configuracion;
use App\Models\MLApp;
use App\Models\MLPregunta;
use App\Models\MLRespuesta;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\MLAppService;
use App\Services\MercadoLibre\PreguntaService;
use Inertia\Inertia;
use Illuminate\Http\Request;


class PreguntasController extends Controller
{

	public function __construct(
		private PreguntaService $preguntaService,
	) {}

	public function index($client_id)
	{
		$datos = [];

		$saludo = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-saludo')
			->first();
		$firma = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-firma')
			->first();
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)->first();
		$query = MLPregunta::where('status', '=', 'UNANSWERED')->with('from_user')
			->with('item')->whereHas('item', function ($query) {
				$query->where('status', 'active');
			})
			->where('seller_id', '=', $cliente->usuario->meli_user_id)
			->orderBy('date_created', 'ASC')->get();

		$datos = new PreguntaCollection(
			$query
		);
		return Inertia::render('MercadoLibre/Preguntas', [
			'client_id' => $client_id,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'items' => $datos,
			'saludo' => $saludo,
			'firma' => $firma
		]);
	}

	public function store($payload)
	{
		$exists = MLPregunta::where('pregunta_id', $payload['id'] ?? null)->exists();
		if ($exists) return;
		$this->preguntaService->updateOrCreate($payload);
	}

	public function responder(Request $request)
	{
		$request->merge(['date_created' => now()]);
		$cliente = MLApp::with('usuario')
			->where('app_id', $request->client_id)->first();

		MLRespuesta::create([
			'pregunta_id' => $request->pregunta_id,
			'from_user_id' => $request->from_user_id,
			'date_created' => $request->date_created,
			'text' => $request->text,
			'payload' => $request->payload,

		]);
		$ml = app(MercadoLibreService::class)->forClient($request->client_id);
		//enviar a mercado libre
		$respuestaML = $ml->apiPost('/answers', [
			'question_id' => $request->pregunta_id,
			'text' => $request->text,

		], $cliente->usuario->meli_user_id);

		$tokenData = $respuestaML;

		$item = MLPregunta::where('pregunta_id', '=', $request->pregunta_id)->first();
		if (!is_null($item)) {
			$item->update([
				'status' => 'ANSWERED',
				'payload' => $tokenData
			]);
		}
	}

	public function bloquearUsuario(Request $request)
	{
		//enviar a mercado libre

	}

	//eliminar
	public function destroy($id)
	{
		//$item = MLPregunta::find($id);
		//$item->delete();
	}

	public function historial($client_id)
	{
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->firstOrFail();

		// 2️⃣ Consultar historial con filtros correctos
		$queryDatos = MLPregunta::with('from_user')
			->with('item')->whereHas('item', function ($query) {
				$query->where('status', 'active');
			})
			->with('respuesta')
			->where('status', '=', 'ANSWERED')
			->where('seller_id', '=', $cliente->usuario->meli_user_id)
			->orderBy('date_created', 'desc')->get();
		// 3️⃣ Collection para Inertia
		$datos = new PreguntaHistorialCollection($queryDatos);

		return Inertia::render('MercadoLibre/PreguntaHistorial', [
			'datos' => $datos
		]);
	}
}
