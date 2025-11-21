<?php

namespace App\Http\Controllers;

use App\Services\MercadoLibreService;

class PusherController extends Controller
{
	public function __construct(
			private	MercadoLibreService $ml
	) {}

	public function index() {}

	public function testNotif($tipo)
	{


		switch ($tipo) {
			case 'envio':
				$this->ml->pusherNotificacion('venta', 'envio');
				break;
			case 'question':
				$this->ml->pusherNotificacion('ml', 'question');
				break;

			default:
				# code...
				break;
		}
	}
}
