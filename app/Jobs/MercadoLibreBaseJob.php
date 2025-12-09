<?php

namespace App\Jobs;

use App\Models\MLApp;
use App\Services\MercadoLibre\MercadoLibreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class MercadoLibreBaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public function backoff()
    {
        return [10, 30, 60, 120, 300];
    }

    public ?string $clientId;
    public ?string $meliUserId;
    public ?string $resourceId;

    /**
     * Constructor flexible: permite jobs con datos (webhook)
     * o sin datos (cron scheduler)
     */
    public function __construct(
        ?string $clientId = null,
        ?string $meliUserId = null,
        ?string $resourceId = null
    ) {
        $this->clientId   = $clientId;
        $this->meliUserId = $meliUserId;
        $this->resourceId = $resourceId;
    }

    /**
     * Obtiene el service para este cliente
     */
    protected function meli(): MercadoLibreService
    {
        if (!$this->clientId) {
            throw new \Exception("Debe definir clientId antes de usar MercadoLibreService (llame a forClient()).");
        }

        return app(MercadoLibreService::class)->forClient($this->clientId);
    }

    /**
     * Obtiene el modelo de cliente
     */
    protected function cliente()
    {
        if (!$this->clientId) return null;

        return MLApp::with('usuario')
            ->where('app_id', $this->clientId)
            ->first();
    }
}
