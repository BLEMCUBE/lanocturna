<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MLApp;
use App\Models\MLClient;
use App\Services\MercadoLibre\MercadoLibreService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class AuthController extends Controller
{
public function __construct(
		private	MercadoLibreService $ml,
	) {}
	public function conectar($id)
	{
		$client = MLApp::where('app_id',$id)->first();
		$query = http_build_query([
			'response_type' => 'code',
			'client_id' => $client->app_id,
			'redirect_uri' => route('mercadolibre.callback'),
			'state' => $client->id, // pasamos el ID del cliente
		]);
		$client->update(['redirect_uri' => route('mercadolibre.callback')]);

		return redirect("https://auth.mercadolibre.com.uy/authorization?$query");
	}

	public function refrecarToken(MLApp $cliente)
	{
		$usuario = MLClient::where('app_id', $cliente->id)->first();
		if (!$usuario == null) {

			$token = $this->ml->forClient($cliente->app_id)->refreshAccessToken($usuario->meli_user_id);
		}
	}
	public function desconectar(MLApp $cliente)
	{
		// Eliminar usuario asociado 1 a 1
		if ($cliente->usuario) {
			$cliente->usuario->delete();
		}
		return redirect()->back()->with('success', 'Cuenta de Mercado Libre desconectada correctamente.');
	}

}
