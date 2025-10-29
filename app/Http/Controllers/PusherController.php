<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\ConfiguracionService;
use Exception;
use Inertia\Inertia;
use Pusher\Pusher;
use Illuminate\Support\Facades\DB;

class PusherController extends Controller
{
	public function __construct(
			private ConfiguracionService $configuracionService
	) {}

	public function index() {}

	public function testNotif($tipo)
	{

		$configuracion = Configuracion::get();

		$options = [
			'cluster' => $this->configuracionService->getOp($configuracion, 'pusher-cluster'),
			'useTLS' => $this->configuracionService->getOp($configuracion, 'pusher-forcetls'),
		];

		$data = [
			'id' => $this->configuracionService->getOp($configuracion, 'pusher-id'),
			'key' => $this->configuracionService->getOp($configuracion, 'pusher-key'),
			'forcetls' => $this->configuracionService->getOp($configuracion, 'pusher-forcetls'),
			'secret' => $this->configuracionService->getOp($configuracion, 'pusher-secret')
		];

		$pusher = new Pusher(
			$data['key'],
			$data['secret'],
			$data['id'],
			$options
		);


		switch ($tipo) {
			case 'envio':
				//dd($tipo);
				$pusher->trigger('venta', 'envio','venta');
				break;
			case 'question':
			$pusher->trigger('ml', 'question','question');
				break;

			default:
				# code...
				break;
		}
	}
}
