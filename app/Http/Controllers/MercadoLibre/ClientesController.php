<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Requests\MLClienteStoreRequest;
use App\Http\Requests\MLClienteUpdateRequest;
use App\Http\Resources\MercadoLibreCollection;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreUsuario;
use App\Services\MercadoLibreService;
use Inertia\Inertia;

class ClientesController extends Controller
{
	public function __construct(
		private	MercadoLibreService $ml,
	) {}
	public function index()
	{
		$items = new MercadoLibreCollection(
			MercadoLibreCliente::with('usuario')->orderBy('nombre', 'ASC')
				->get()
		);
		return Inertia::render('MercadoLibre/IndexClientes', [
			'items' => $items
		]);
	}

	public function store(MLClienteStoreRequest $request)
	{
		$request->merge(['redirect_uri' => route('mercadolibre.callback')]);
		MercadoLibreCliente::create($request->all());
	}

	public function show($id)
	{
		$item = MercadoLibreCliente::findOrFail($id);
		return response()->json([
			"item" => $item
		]);
	}

	public function update(MLClienteUpdateRequest $request, $id)
	{
		$tipo_cambio = MercadoLibreCliente::findOrFail($id);
		$request->merge(['redirect_uri' => route('mercadolibre.callback')]);
		$tipo_cambio->update($request->all());
	}

	public function destroy($id)
	{
		$item = MercadoLibreCliente::find($id);
		$item->delete();
	}

	public function conectar($id)
	{
		$client = MercadoLibreCliente::findOrFail($id);
		$query = http_build_query([
			'response_type' => 'code',
			'client_id' => $client->client_id,
			'redirect_uri' => route('mercadolibre.callback'),
			'state' => $client->id, // pasamos el ID del cliente
		]);
		$client->update(['redirect_uri' => route('mercadolibre.callback')]);

		return redirect("https://auth.mercadolibre.com.uy/authorization?$query");
	}

	public function refrecarToken(MercadoLibreCliente $cliente)
	{
		$usuario = MercadoLibreUsuario::where('cliente_id', $cliente->id)->first();
		if (!$usuario == null) {
			$token = $this->ml->refreshAccessToken($usuario->meli_user_id);
		}
	}
	public function desconectar(MercadoLibreCliente $cliente)
	{
		// Eliminar usuario asociado 1 a 1
		if ($cliente->usuario) {
			$cliente->usuario->delete();
		}
		return redirect()->back()->with('success', 'Cuenta de Mercado Libre desconectada correctamente.');
	}
}
