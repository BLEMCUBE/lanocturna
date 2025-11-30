<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncProductosTienda;

class ConsultarWooSkusCommand extends Command
{
    protected $signature = 'woo:consultar-skus';
    protected $description = 'Consulta SKUs de Woo y actualiza stock';

    public function handle()
    {
		dispatch((new SyncProductosTienda())->onQueue('meli'));

        $this->info('Job SyncProductosTienda enviado a la cola');

        return Command::SUCCESS;
    }
}
