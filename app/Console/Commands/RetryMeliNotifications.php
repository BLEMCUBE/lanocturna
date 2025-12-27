<?php

namespace App\Console\Commands;

use App\Models\MLNotificacion;
use Illuminate\Console\Command;
use App\Jobs\ProcessMercadoLibreNotification;

class RetryMeliNotifications extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:retry-meli-notifications';

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
		$this->info('Buscando notificaciones pendientes...');

		$notifications = MLNotificacion::
			select('status','id','payload')
			->where('status','received')
		->get();

		if ($notifications->isEmpty()) {
			$this->info('✅ No hay notificaciones pendientes.');
			return;
		}

		foreach ($notifications as $notif) {
			dispatch((new ProcessMercadoLibreNotification($notif->payload))->onQueue('meli'));
			$this->info("📨 Notificación {$notif->id} enviada a la cola.");
		}
		$notifications2 = MLNotificacion::
			select('status','id','payload')
			->where('status', 'received')
		->count();

		$this->info("{$notifications2} Todas las notificaciones fueron encoladas.");
	}
}
