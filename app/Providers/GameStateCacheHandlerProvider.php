<?php declare(strict_types=1);

namespace App\Providers;

use App\State\Handlers\GameStateCacheHandler;
use App\State\Handlers\GameStateCacheHandlerInterface;
use Illuminate\Support\ServiceProvider;

class GameStateCacheHandlerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GameStateCacheHandlerInterface::class, GameStateCacheHandler::class);
    }
}
