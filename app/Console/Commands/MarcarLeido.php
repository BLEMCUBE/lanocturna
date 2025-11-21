<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessActualizarMensajeExistente;

class MarcarLeido extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'datos:mensajes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'vuelve a consultar a las notificaciones';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{

			//dispatch((new ProcessActualizarMensajeExistente())->onQueue('meli'));
			//ProcessActualizarMensajeExistente::dispatch()->onQueue('meli');

	}
}
