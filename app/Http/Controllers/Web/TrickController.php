<?php declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trick\PlayCardRequest;
use App\Jobs\Trick\PlayCard;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class TrickController extends Controller
{
    public function playCard(PlayCardRequest $request): Response
    {
        $game = Bus::dispatch(new PlayCard($request->getGameId(), $request->getAuthUserId(), $request->getCardSuit(), $request->getCardValue()));

        return Redirect::route('game', ['game' => $game->getKey()]);
    }
}
