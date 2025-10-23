<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreguntaCollection;
use App\Models\Configuracion;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreItem;
use App\Models\MercadoLibreListaUsuario;
use App\Models\MercadoLibrePregunta;
use App\Models\RespuestaRapida;
use App\Services\ItemService;
use App\Services\ListaUsuarioService;
use App\Services\MercadoLibreService;
use App\Services\PreguntaService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;


class PreguntasController extends Controller
{
	protected $ml;
	protected $preguntaService;

	public function __construct(
		MercadoLibreService $ml,
		PreguntaService $preguntaService,
		private ItemService $itemService,
		private ListaUsuarioService $listaUsuarioService,
	) {
		$this->ml = $ml;
		$this->preguntaService = $preguntaService;
	}

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
$repuesta_rapidas=RespuestaRapida::select('id', 'titulo', 'descripcion', 'color')->orderBy('titulo', 'ASC')->get();
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
			'repuesta_rapidas'=>$repuesta_rapidas
		]);
	}

	public function store($payload, $userId)
	{
		$exists = MercadoLibrePregunta::where('mercadolibre_pregunta_id', $payload['id'] ?? null)->exists();
		//if ($exists) return;
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
		$this->preguntaService->updateOrCreate($payload);

		$exists = MercadoLibreItem::where('item_id', $question['item_id'] ?? null)->exists();

		if (!$exists) {
			$item = $this->ml->apiGet('/items/' . $question['item_id'], $userId);
			$this->itemService->updateOrCreate($item);
		}
		//usuario
		$existsUser = MercadoLibreListaUsuario::where('user_id', $payload['from']['id'] ?? null)->exists();

		if (!$existsUser) {
			$itemUser = $this->ml->apiGet('/users/' . $payload['from']['id'], $userId);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}
		Log::info("Pregunta registrada [{$question['id']}]");
	}

	//eliminar
	public function destroy($id)
	{
		$item = MercadoLibrePregunta::find($id);
		$item->delete();
	}
}
