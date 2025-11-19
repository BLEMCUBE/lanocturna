<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class UtcToLocalCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (!$value) return null;

        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (!$value) return null;

        // siempre guardar en UTC
        return Carbon::parse($value)->setTimezone('UTC');
    }
}
