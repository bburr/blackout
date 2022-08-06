<?php declare(strict_types=1);

namespace App\State\Handlers;

use Illuminate\Support\Facades\Cache;

class GameStateCacheHandler implements GameStateCacheHandlerInterface
{
    public function __construct(protected string $gameKey)
    {
    }

    protected function cacheKey(string $cacheKey): string
    {
        return $cacheKey . $this->gameKey;
    }

    public function cacheGet(string $key): mixed
    {
        return json_decode(Cache::get($this->cacheKey($key)), true);
    }

    public function cacheHas(string $key): bool
    {
        return Cache::has($key);
    }

    public function cachePut(string $key, mixed $value): void
    {
        Cache::put($this->cacheKey($key), json_encode($value));
    }
}
