<?php

namespace App\Console\Commands;

use App\Models\MLNotificacion;
use Illuminate\Console\Command;
use Carbon\Carbon;

class EliminarNotificacionesAntiguas extends Command
{
	/**
	 * Nombre y firma del comando
	 *
	 * @var string
	 */
	protected $signature = 'datos:eliminar-notificaciones-antiguas';

	/**
	 * Descripción
	 *
	 * @var string
	 */
	protected $description = 'Elimina los registros con más de 2 días de antigüedad (contando el día actual)';

	/**
	 * Ejecuta el comando
	 */
	public function handle()
	{
		$fechaLimite = Carbon::now()->subDays(2)->startOfDay();

		$total = MLNotificacion::where('created_at', '<', $fechaLimite)->delete();

		$this->info("✅ Se eliminaron {$total} registros creados antes de {$fechaLimite->toDateString()}.");

		return Command::SUCCESS;
	}
}
