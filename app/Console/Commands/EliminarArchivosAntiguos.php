<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class EliminarArchivosAntiguos extends Command
{
	protected $signature = 'log:limpiar';
	protected $description = 'Elimina archivos de más de 1 mes';

	public function handle()
	{
		$path = storage_path('logs');
		//$limite = now()->subDays(2)->startOfDay(); // hace 2 dias
		$limite = now()->subMonth(); // hace 1 mes

		foreach (File::files($path) as $file) {

			$filename = $file->getFilename(); // ejemplo: laravel-2025-11-30.log

			// Obtener la fecha desde el nombre
			if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', $filename, $m)) {

				$fechaArchivo = Carbon::createFromFormat('Y-m-d', $m[1]);

				// Eliminar si es menor a hace 2 meses
				if ($fechaArchivo->lt($limite)) {
					File::delete($file->getRealPath());
				}
			}
		}

		return Command::SUCCESS;
	}
}
