<?php declare(strict_types=1);

namespace App\Jobs\Trick;

use App\Events\CardWasPlayed;
use App\Exceptions\GameIsCompleteException;
use App\Jobs\FinishGame;
use App\Jobs\MakePlayForNextPlayer;
use App\Models\Game;
use App\State\CardState;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class PlayCard
{
    public function __construct(protected string $gameId, protected string $authUserId, protected string $cardSuit, protected int $cardValue)
    {
    }

    public function handle(): Game
    {
        /** @var Game|null $game */
        $game = Game::find($this->gameId);

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game);

        abort_unless($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is still ongoing for current round');

        $player = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToPlay());

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'It is not the time to play a card');
        abort_unless($player->getUser()->getKey() === $this->authUserId, Response::HTTP_BAD_REQUEST, 'It is not your turn to play');

        try {
            Bus::dispatch(new MakePlayForNextPlayer($gameState, $player, new CardState($this->cardSuit, $this->cardValue)));
        }
        catch (GameIsCompleteException $e) {
            Bus::dispatch(new FinishGame($gameState));
        }

        $gameState->save();

        broadcast(new CardWasPlayed($game))->toOthers();

        return $game;
    }
}
