<?php declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Round\PerformBetRequest;
use App\Jobs\Round\PerformBet;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RoundController extends Controller
{
    public function performBet(PerformBetRequest $request): Response
    {
        $game = Bus::dispatch(new PerformBet($request->getGameId(), $request->getAuthUserId(), $request->getBet()));

        return Redirect::route('game', ['game' => $game->getKey()]);
    }
}
