<?php declare(strict_types=1);

namespace App\Jobs\Round;

use App\Events\BetWasMade;
use App\Jobs\MakeBetForNextPlayer;
use App\Models\Game;
use App\State\GameState;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class PerformBet
{
    public function __construct(protected string $gameId, protected string $authUserId, protected int $bet)
    {
    }

    public function handle(): Game
    {
        /** @var Game|null $game */
        $game = Game::find($this->gameId);

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game);

        abort_if($gameState->getCurrentRound()->isBettingDone(), Response::HTTP_BAD_REQUEST, 'Betting is done for current round');

        $bettingPlayer = $gameState->getPlayerAtIndex($gameState->getCurrentRound()->getNextPlayerIndexToBet());

        abort_if($bettingPlayer === null, Response::HTTP_BAD_REQUEST, 'It is not the time to bet');
        abort_unless($bettingPlayer->getUser()->getKey() === $this->authUserId, Response::HTTP_BAD_REQUEST, 'It is not your turn to bet');

        Bus::dispatch(new MakeBetForNextPlayer($gameState, $this->bet));

        $gameState->save();

        broadcast(new BetWasMade($game))->toOthers();

        return $game;
    }
}
