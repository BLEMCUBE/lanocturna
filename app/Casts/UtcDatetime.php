<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Carbon\Carbon;

class UtcDatetime implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (!$value) return null;

        // Interpretar SIEMPRE como UTC
        return Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC')
                     ->setTimezone(config('app.timezone'));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value) return null;

        // Convertir a UTC al guardar
        return Carbon::parse($value)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}
