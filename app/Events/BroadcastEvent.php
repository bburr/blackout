<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

abstract class BroadcastEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;
}
