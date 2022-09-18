<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\GetHandAsUserRequest;
use App\Http\Requests\Player\GetHandRequest;
use App\Models\Game;
use App\State\GameState;
use App\State\PlayerState;
use Symfony\Component\HttpFoundation\Response;

class PlayerController extends Controller
{
    public function getHand(GetHandRequest $request): Response
    {
        /** @var Game|null $game */
        $game = Game::find($request->getGameId());

        abort_if($game === null, Response::HTTP_NOT_FOUND, 'No game found');

        $gameState = new GameState($game);

        $player = $gameState->getPlayers()->firstWhere(fn (PlayerState $playerState) => $playerState->getUser()->getKey() === $request->getAuthUserId());

        abort_if($player === null, Response::HTTP_BAD_REQUEST, 'You are not in that game');

        return response()->json($player->getHand());
    }

    public function getHandAsUser(GetHandAsUserRequest $request): Response
    {
        return $this->getHand($request);
    }
}
