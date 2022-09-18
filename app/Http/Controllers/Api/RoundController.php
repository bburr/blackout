<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Round\PerformBetAsUserRequest;
use App\Http\Requests\Round\PerformBetRequest;
use App\Jobs\Round\PerformBet;
use Illuminate\Support\Facades\Bus;

class RoundController extends Controller
{
    public function performBet(PerformBetRequest $request): void
    {
        Bus::dispatch(new PerformBet($request->getGameId(), $request->getAuthUserId(), $request->getBet()));
    }

    public function performBetAsUser(PerformBetAsUserRequest $request): void
    {
        $this->performBet($request);
    }
}
