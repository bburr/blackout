<?php

namespace App\State\Handlers;

interface GameStateCacheHandlerInterface
{
    public function cacheGet(string $key): mixed;

    public function cacheHas(string $key): bool;

    public function cachePut(string $key, mixed $value): void;
}
