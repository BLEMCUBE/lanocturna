<?php

namespace App\Console\Commands;


use App\Models\MLItem;
use Illuminate\Console\Command;
use App\Jobs\DetalleItemJob;

class FetchItemsPaused extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'datos:items-paused';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'vuelve a consultar a los productos pausados';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{

		$items =MLItem::where('status', '=', 'paused')
			->select('item_id','seller_id', 'status')->get();

		if ($items->isEmpty()) {
			$this->info('✅ No hay notificaciones pendientes.');
			return;
		}

		foreach ($items as $notif) {
			$this->info("dsd {$notif->item_id}");
			dispatch((new DetalleItemJob($notif->item_id,$notif->seller_id))->onQueue('meli'));
			$this->info("📨 Notificación {$notif->item_id} enviada a la cola.");
		}

	}
}
