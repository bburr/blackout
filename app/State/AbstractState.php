<?php declare(strict_types=1);

namespace App\State;

use Illuminate\Support\Facades\Cache;
use JsonSerializable;

abstract class AbstractState implements JsonSerializable
{
    protected function cacheKey(string $cacheKey): string
    {
        return $cacheKey;
    }

    protected function cacheGet(string $key): mixed
    {
        return json_decode(Cache::get($this->cacheKey($key)), true);
    }

    protected function cachePut(string $key, mixed $value): void
    {
        Cache::put($this->cacheKey($key), json_encode($value));
    }
}
