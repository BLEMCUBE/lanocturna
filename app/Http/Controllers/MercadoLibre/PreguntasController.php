<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreguntaCollection;
use App\Http\Resources\PreguntaHistorialCollection;
use App\Jobs\FetchMercadoLibrePreguntasHistorial;
use App\Models\Configuracion;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreListaUsuario;
use App\Models\MercadoLibrePregunta;
use App\Models\MercadoLibreRespuesta;
use App\Services\MercadoLibreService;
use App\Services\PreguntaService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Http\Request;


class PreguntasController extends Controller
{

	public function __construct(
		private	MercadoLibreService $ml,
		private PreguntaService $preguntaService,
	) {}

	public function index()
	{
		$datos = [];

		$saludo = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-saludo')
			->first();
		$firma = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-firma')
			->first();

		$datos = new PreguntaCollection(
			MercadoLibrePregunta::where('status', '=', 'UNANSWERED')->with('from_user')->with('item')->whereHas('item', function ($query) {
				$query->where('status', 'active');
			})
				->orderBy('date_created', 'ASC')->get()
		);
		return Inertia::render('MercadoLibre/Preguntas', [
			'items' => $datos,
			'saludo' => $saludo,
			'firma' => $firma
		]);
	}

	public function store($payload)
	{
		$exists = MercadoLibrePregunta::where('mercadolibre_pregunta_id', $payload['id'] ?? null)->exists();
		if ($exists) return;
		$this->preguntaService->updateOrCreate($payload);
	}

	public function responder(Request $request)
	{
		$request->merge(['date_created' => now()]);
		$cliente = MercadoLibreCliente::with('usuario')->first();

		MercadoLibreRespuesta::create([
			'mercadolibre_pregunta_id' => $request->mercadolibre_pregunta_id,
			'from_user_id' => $request->from_user_id,
			'date_created' => $request->date_created,
			'text' => $request->text,
			'payload' => $request->payload,

		]);

		//enviar a mercado libre
		$respuestaML = $this->ml->apiPost('/answers', [
			'question_id' => $request->mercadolibre_pregunta_id,
			'text' => $request->text,

		], $cliente->usuario->meli_user_id);

		$tokenData = $respuestaML;

		$item = MercadoLibrePregunta::where('mercadolibre_pregunta_id', '=', $request->mercadolibre_pregunta_id)->first();
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
		//$item = MercadoLibrePregunta::find($id);
		//$item->delete();
	}

	public function historial()
	{
		$items = MercadoLibrePregunta::select('item_id', 'from_user_id')->groupBy('item_id')->get();

		foreach ($items as $key => $value) {
			$user = MercadoLibreListaUsuario::where('user_id', $value->from_user_id)->first();
			if (is_null($user)) {

				FetchMercadoLibrePreguntasHistorial::dispatch($value->item_id)->onQueue('meli');
			}
		}

		$queryDatos = MercadoLibrePregunta::with(['from_user', 'respuesta', 'item'])
			->whereHas('item', function ($q) {
				$q->where('status', 'active');
			})
			->where('status', '=', 'UNANSWERED')
		->orWhere('status', '=', 'ANSWERED')
			->whereHas('respuesta') // <-- Solo preguntas que tengan respuesta
			->orderBy('date_created', 'ASC')
			->get();



		$datos = new PreguntaHistorialCollection(
			$queryDatos
		);

		return Inertia::render('MercadoLibre/PreguntaHistorial', [
			'datos' => $datos,
		]);
	}
}
