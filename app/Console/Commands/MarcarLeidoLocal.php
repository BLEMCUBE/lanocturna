<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MercadoLibreMensaje;

class MarcarLeidoLocal extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'datos:mensajesleidos';

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

			//MercadoLibreMensaje::where('is_read','=',0)->update(["is_read"=>1]);
		//	 return Command::SUCCESS;

	}
}
