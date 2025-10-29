<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreguntaCollection;
use App\Models\Configuracion;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreItem;
use App\Models\MercadoLibreListaUsuario;
use App\Models\MercadoLibrePregunta;
use App\Models\MercadoLibreRespuesta;
use App\Models\RespuestaRapida;
use App\Services\ItemService;
use App\Services\ListaUsuarioService;
use App\Services\MercadoLibreService;
use App\Services\PreguntaService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;


class PreguntasController extends Controller
{

	public function __construct(
		private	MercadoLibreService $ml,
		private PreguntaService $preguntaService,
		private ItemService $itemService,
		private ListaUsuarioService $listaUsuarioService,
	) {}

	public function index()
	{
		$cliente = MercadoLibreCliente::with('usuario')->first();
		$datos = [];

		$saludo = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-saludo')
			->first();
		$firma = Configuracion::select('slug', 'value')
			->where('slug', 'pregunta-firma')
			->first();

		$fechaInicio = Carbon::now()
			->subDays(15)
			->startOfDay()
			->format('Y-m-d\TH:i:s.vP');
		$fechaFin = Carbon::now()
			->setHour(23)
			->setMinute(59)
			->setSecond(0)
			->format('Y-m-d\TH:i:s.vP');


		if (!is_null($cliente->usuario)) {
			$parametros = [
				'seller_id' => $cliente->usuario->meli_user_id,
				'status' => 'UNANSWERED',
				'api_version' => '4',
				'date_created.from' => $fechaInicio,
				'date_created.to' => $fechaFin,
			];
			//$query_questions = $this->ml->apiGet('/questions/search', $cliente->usuario->meli_user_id, $parametros);

			$query_questions = null;
			if (!is_null($query_questions)) {
				foreach ($query_questions['questions'] as $key => $value) {
					$this->store($value, $cliente->usuario->meli_user_id);
				}
			}
		}
		$repuesta_rapidas =	RespuestaRapida::select('id', 'titulo', 'descripcion', 'color')->orderBy('titulo', 'ASC')->get();
		$datos = new PreguntaCollection(
			MercadoLibrePregunta::where('status', '=', 'UNANSWERED')->with('from_user')->with('item')->whereHas('item', function ($query) {
				$query->where('status', 'active');
			})
				->orderBy('date_created', 'DESC')->get()
		);
		return Inertia::render('MercadoLibre/Preguntas', [
			'items' => $datos,
			'saludo' => $saludo,
			'firma' => $firma,
			'repuesta_rapidas' => $repuesta_rapidas
		]);
	}

	public function store($payload, $userId)
	{
		$exists = MercadoLibrePregunta::where('mercadolibre_pregunta_id', $payload['id'] ?? null)->exists();
		if ($exists) return;
		//try {

		//crear pregunta
		$this->preguntaService->updateOrCreate($payload);

		// item
		$existsItem = MercadoLibreItem::where('item_id', $question['item_id'] ?? null)->exists();

		if (!$existsItem) {
			$item = $this->ml->apiGet('/items/' . $payload['item_id'], $userId);
			$this->itemService->updateOrCreate($item);
		}

		//usuario
		$existsUser = MercadoLibreListaUsuario::where('user_id', $payload['from']['id'] ?? null)->exists();

		if (!$existsUser) {
			$itemUser = $this->ml->apiGet('/users/' . $payload['from']['id'], $userId);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}


		Log::info("Pregunta registrada [{$payload['id']}]");
		/*} catch (\Exception $e) {
			Log::error("Error guardando pregunta: " . $e->getMessage());
		}*/
	}
	public function storeNotificacion($payload)
	{
		$resource = $payload['resource'] ?? null;
		$userId   = $payload['user_id'] ?? null;

		if (!$resource || !$userId) return;
		//try {
		$question = $this->ml->apiGet($resource, $userId);
		//crear pregunta
		$this->preguntaService->updateOrCreate($question);

		$exists = MercadoLibreItem::where('item_id', $question['item_id'] ?? null)->exists();

		if (!$exists) {
			$item = $this->ml->apiGet('/items/' . $question['item_id'], $userId);
			$this->itemService->updateOrCreate($item);
		}
		//usuario
		if (!is_null($question)) {
		$existsUser = MercadoLibreListaUsuario::where('user_id', $question['from']['id'] ?? null)->exists();

		if (!$existsUser) {
			$itemUser = $this->ml->apiGet('/users/' . $question['from']['id'], $userId);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}
		}
		Log::info("Pregunta registrada Notificacion [{$question['id']}]");
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

		//cambiar a respondido la pregunta
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
		dd($request);
		//enviar a mercado libre

	}

	//eliminar
	public function destroy($id)
	{
		$item = MercadoLibrePregunta::find($id);
		$item->delete();
	}
}
