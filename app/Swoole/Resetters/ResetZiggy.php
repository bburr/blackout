<?php declare(strict_types=1);

namespace App\Swoole\Resetters;

use Illuminate\Contracts\Container\Container;
use SwooleTW\Http\Server\Resetters\ResetterContract;
use SwooleTW\Http\Server\Sandbox;
use Tightenco\Ziggy\BladeRouteGenerator;

class ResetZiggy implements ResetterContract
{
    public function handle(Container $app, Sandbox $sandbox): void
    {
        BladeRouteGenerator::$generated = false;
    }
}
