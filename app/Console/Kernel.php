<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * Define the application's command schedule.
	 */
	protected function schedule(Schedule $schedule): void
	{
		$schedule->command('datos:eliminar-notificaciones-antiguas')->dailyAt('01:00');
		$schedule->command('log:limpiar')->daily();
		$schedule->command('woo:consultar-skus')->dailyAt('02:00');
		//$schedule->command('woo:sincronizar-stock')->dailyAt('02:00');
		$schedule->job(new \App\Jobs\FetchUnreadQuestionsJob, 'meli')->everyTwoMinutes()
        ->withoutOverlapping()
        ->onOneServer();
		$schedule->job(new \App\Jobs\CheckMeliPackStatus, 'meli')->everyTwoMinutes()
        ->withoutOverlapping()
        ->onOneServer();
	}

	/**
	 * Register the commands for the application.
	 */
	protected function commands(): void
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
