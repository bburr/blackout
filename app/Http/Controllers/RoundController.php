<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\BetWasMade;
use App\Http\Requests\Round\PerformBet;
use App\Http\Requests\Round\PerformBetAsUser;
use App\Jobs\MakeBetForNextPlayer;
use App\Models\Game;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RoundController extends Controller
{
    public function performBet(PerformBet $request): Response
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('gameId'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game);

        abort_if($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is done for current round');

        $bettingPlayer = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToBet());

        abort_if($bettingPlayer === null, Response::HTTP_BAD_REQUEST, 'It is not the time to bet');
        abort_unless($bettingPlayer->getUser()->getKey() === $request->get('auth_user_id'), Response::HTTP_BAD_REQUEST, 'It is not your turn to bet');

        Bus::dispatch(new MakeBetForNextPlayer($gameState, $request->get('bet')));

        $gameState->save();

        broadcast(new BetWasMade($game));//->toOthers();

        return Redirect::route('game', ['game' => $game->getKey()]);
    }

    public function performBetAsUser(PerformBetAsUser $request): void
    {
        $this->performBet($request);
    }
}
