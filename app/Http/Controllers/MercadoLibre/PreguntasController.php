<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreguntaCollection;
use App\Models\Configuracion;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibrePregunta;
use App\Models\MercadoLibreRespuesta;
use App\Services\MercadoLibreService;
use App\Services\PreguntaService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
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
			$query_questions = $this->ml->apiGet('/questions/search', $cliente->usuario->meli_user_id, $parametros);

			//$query_questions = null;
			if (!is_null($query_questions)) {
				foreach ($query_questions['questions'] as $key => $value) {
					$this->store($value, $cliente->usuario->meli_user_id);
				}
			}
		}

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

		//cambiar a respondido la pregunta
		$item = MercadoLibrePregunta::where('mercadolibre_pregunta_id', '=', $request->mercadolibre_pregunta_id)->first();
		if (!is_null($item)) {
			$item->update([
				'status' => 'ANSWERED',
				'payload' => $tokenData
			]);
		}
	}

	public function cambiarEstado($id)
	{
		$cliente = MercadoLibreCliente::with('usuario')->first();
		$query_question = $this->ml->apiGet('/questions/' . $id, $cliente->usuario->meli_user_id);

		$this->preguntaService->updateOrCreate($query_question);

		if (!$query_question['answer'] == null) {

			MercadoLibreRespuesta::create([
				'mercadolibre_pregunta_id' => $query_question['id'],
				'from_user_id' => $query_question['from']['id'],
				'date_created' =>  $query_question['answer']['date_created'],
				'text' =>  $query_question['answer']['text'],
				'payload' => $query_question['answer'],

			]);
		};
	}


	public function bloquearUsuario(Request $request)
	{
		//enviar a mercado libre

	}

	//eliminar
	public function destroy($id)
	{
		$item = MercadoLibrePregunta::find($id);
		$item->delete();
	}
}
