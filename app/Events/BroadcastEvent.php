<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class BroadcastEvent implements ShouldBroadcast
{
    use InteractsWithSockets;
}
