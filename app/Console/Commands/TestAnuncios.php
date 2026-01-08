<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\RunFetchPublicidadForAllClientsJob;

class TestAnuncios extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'test:anuncios';



	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		dispatch((new RunFetchPublicidadForAllClientsJob())->onQueue('meli'));
		$this->info('Job RunFetchPublicidadForAllClientsJob enviado a la cola');

		return Command::SUCCESS;
	}
}
