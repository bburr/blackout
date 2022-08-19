<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\CardWasPlayed;
use App\Exceptions\GameIsCompleteException;
use App\Http\Requests\Trick\PlayCard;
use App\Http\Requests\Trick\PlayCardAsUser;
use App\Jobs\FinishGame;
use App\Jobs\MakePlayForNextPlayer;
use App\Models\Game;
use App\State\CardState;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class TrickController extends Controller
{
    public function playCard(PlayCard $request): Response
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('gameId'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game);

        abort_unless($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is still ongoing for current round');

        $player = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToPlay());

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'It is not the time to play a card');
        abort_unless($player->getUser()->getKey() === $request->get('auth_user_id'), Response::HTTP_BAD_REQUEST, 'It is not your turn to play');

        try {
            Bus::dispatch(new MakePlayForNextPlayer($gameState, $player, new CardState($request->get('cardSuit'), $request->get('cardValue'))));
        }
        catch (GameIsCompleteException $e) {
            Bus::dispatch(new FinishGame($gameState));
        }

        $gameState->save();

        broadcast(new CardWasPlayed($game));//->toOthers();

        return Redirect::route('game', ['game' => $game->getKey()]);
    }

    public function playCardAsUser(PlayCardAsUser $request): void
    {
        $this->playCard($request);
    }
}
