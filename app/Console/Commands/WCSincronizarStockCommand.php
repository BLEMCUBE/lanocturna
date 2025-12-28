<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use App\Jobs\WCActualizarStockJob;

class WCSincronizarStockCommand extends Command
{
    protected $signature = 'woo:sincronizar-stock';
    protected $description = 'Actualiza stock en WooCommerce desde productos locales';

    public function handle()
    {
		 // Obtener productos que quieras sincronizar
        $productos = Producto::where('is_store',1)->select('origen','stock')->get();

        foreach ($productos as $producto) {

		dispatch((new WCActualizarStockJob($producto->origen,$producto->stock))->onQueue('meli'));

		}

        $this->info('Job enviado a cola.');
    }
}
