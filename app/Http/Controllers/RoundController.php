<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Round\PerformBet;
use App\Http\Requests\Round\PerformBetAsUser;
use App\Http\Requests\Round\PlayCard;
use App\Http\Requests\Round\PlayCardAsUser;
use App\Http\Requests\Round\StartNextRound;
use App\Http\Requests\Round\StartNextRoundAsUser;
use App\Jobs\MakeBetForNextPlayer;
use App\Jobs\NextRound;
use App\Models\Game;
use App\State\CardState;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class RoundController extends Controller
{
    public function performBet(PerformBet $request): void
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('game_id'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game, null);

        abort_if($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is done for current round');

        $bettingPlayer = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToBet());

        abort_if($bettingPlayer === null, Response::HTTP_BAD_REQUEST, 'It is not the time to bet');
        abort_unless($bettingPlayer->getUser()->getKey() === $request->get('auth_user_id'), Response::HTTP_BAD_REQUEST, 'It is not your turn to bet');

        Bus::dispatch(new MakeBetForNextPlayer($gameState, $request->get('bet')));

        $gameState->save();
    }

    public function performBetAsUser(PerformBetAsUser $request): void
    {
        $this->performBet($request);
    }

    public function playCard(PlayCard $request): void
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('game_id'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game, null);

        abort_unless($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is still ongoing for current round');

        $player = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToPlay());

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'It is not the time to play a card');
        abort_unless($player->getUser()->getKey() === $request->get('auth_user_id'), Response::HTTP_BAD_REQUEST, 'It is not your turn to play');

        $gameState->makePlayForNextPlayer(new CardState($request->get('card_suit'), $request->get('card_value')));

        $gameState->save();
    }

    public function playCardAsUser(PlayCardAsUser $request): void
    {
        $this->playCard($request);
    }

    public function startNextRound(StartNextRound $request): void
    {
        /** @var Game|null $game */
        $game = Game::find($request->get('game_id'));

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');
        dump($game->getLobby()->getUsers()->firstWhere('pivot.is_owner', '=', true)->getKey(), $request->get('auth_user_id'));
        abort_unless($game->getLobby()->getUsers()->firstWhere('pivot.is_owner', '=', true)->getKey() === $request->get('auth_user_id'), Response::HTTP_UNAUTHORIZED, 'You are not the lobby owner');

        $gameState = new GameState($game, null);

        // todo this check is wrong - do not start next round until all hands are empty
        abort_unless($gameState->getCurrentRound()->isPlayDone(), Response::HTTP_BAD_REQUEST, 'The current round is not yet done');

        Bus::dispatch(new NextRound($gameState));
    }

    public function startNextRoundAsUser(StartNextRoundAsUser $request): void
    {
        $this->startNextRound($request);
    }
}
